<?php
/*** Copyright © Ulmod. All rights reserved. **/

namespace Ulmod\OrderImportExport\Console\Command;

use Ulmod\OrderImportExport\Exception\ImportException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;
use Ulmod\OrderImportExport\Model\File as FileModel;
use Ulmod\OrderImportExport\Framework\File\Csv as CsvProcessor;
use Ulmod\OrderImportExport\Api\ImporterInterfaceFactory;
use Ulmod\OrderImportExport\Api\Data\ImportConfigInterfaceFactory;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Area as AppArea;
use Ulmod\OrderImportExport\Model\ImportlogFactory;
use Ulmod\OrderImportExport\Model\Logger\ImportLogger;
use Ulmod\OrderImportExport\Model\Config as SystemConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Import extends \Symfony\Component\Console\Command\Command
{
    /**
     * Command name for cli
     */
    const COMMAND_NAME = 'ulmod:order:import-single';

    /**
     * Command description in cli
     */
    const COMMAND_DESCRIPTION = 'Ulmod Order Import to CSV (Single CSV file import)';
    
    /**
     * @var FileModel
     */
    private $fileModel;

    /**
     * @var CsvProcessor
     */
    private $csvProcessor;

    /**
     * @var ImporterInterfaceFactory
     */
    private $importerFactory;

    /**
     * @var ImportConfigInterfaceFactory
     */
    private $configFactory;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var ImportlogFactory
     */
    protected $importlogFactory;

    /**
     * @var ImportLogger
     */
    protected $importLogger;   
 
    /**
     * @var SystemConfig
     */
    protected $systemConfig;   

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;


    /**
     * @param FileModel $fileModel
     * @param CsvProcessor $csvProcessor
     * @param ImporterInterfaceFactory $importerFactory
     * @param ImportConfigInterfaceFactory $configFactory
     * @param AppState $state
     * @param null|string $name
     */
    public function __construct(
        FileModel $fileModel,
        CsvProcessor $csvProcessor,
        ImporterInterfaceFactory $importerFactory,
        ImportConfigInterfaceFactory $configFactory,
        AppState $state,
        ImportlogFactory $importlogFactory,
        ImportLogger $importLogger,
        SystemConfig $systemConfig,
        Filesystem $filesystem,         
        $name = null
    ) {
        parent::__construct($name);
        $this->fileModel      = $fileModel;
        $this->csvProcessor    = $csvProcessor;
        $this->importerFactory = $importerFactory;
        $this->configFactory   = $configFactory;
        $this->state           = $state;
        $this->importlogFactory = $importlogFactory;
        $this->importLogger = $importLogger;
        $this->systemConfig = $systemConfig; 
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);            
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'filename',
                null,
                InputOption::VALUE_REQUIRED,
                'Relative filepath to file being imported. Eg. --filename=var/ulmod_orderimport/import-orders-yyyy-mm-dd.csv'
            ),
           
            new InputOption(
                'delimiter',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV field separator. Eg. --delimiter=,'
            ),
            new InputOption(
                'enclosure',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV field enclosure. Eg. --enclosure="'
            ),
           
            new InputOption(
                'create-invoices',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enter 1 for YES and 0 for NO. Eg. --create-invoices=1'
            ),
            new InputOption(
                'create-shipments',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enter 1 for YES and 0 for NO. Eg. --create-shipments=1'
            ),
            new InputOption(
                'create-credit-memos',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enter 1 for YES and 0 for NO. Eg. --create-credit-memos=1'
            ),
            new InputOption(
                'error-limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of errors that can occur before the import is stopped/canceled. Eg. --error-limit=4'
            )
        ];
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var \Ulmod\OrderImportExport\Model\Importlog $importlogModel */
        $importlogModel = $this->importlogFactory->create();        
      
        $importType = 2;          
        $executionTime = '00:00:00';
        $currentAdminUsername = 'No';

        $fileDir = $input->getOption('filename');     
        $importedFileAndSize = $input->getOption('filename') .' | Check the size at the imported directory';

        $imported = 0;
        $importError = 0;
        $error = 0;
        
        $messages = [
            'error'   => [],
            'success' => []
        ];        

        try {
            $this->state->setAreaCode(
                AppArea::AREA_ADMINHTML
            );
        } catch (\Exception $e) {
        }

        $output->writeln("Import started");
        try {

            // set created log date here to diff excecution time
            $startTime = microtime(true);
            if ($this->systemConfig->isImportLogEnabled()) {
                $importlogModel->setCreatedAt($startTime);
            }

            $fileNameOption = $input->getOption('filename');
            if (!$fileNameOption) {
                throw new LocalizedException(
                    __('You must enter the filepath to import')
                );
            }

            $filepath = $this->fileModel->getAbsolutePath($fileNameOption);
            $isFileExists = $this->fileModel->fileExists($filepath);
            if (!$isFileExists) {
                throw new LocalizedException(
                    __(
                        '%1 does not exist on the server',
                        $filepath
                    )
                );
            }

            /** @var \Ulmod\OrderImportExport\Api\Data\ImportConfigInterface $config */
            $config = $this->configFactory->create();
            
            $enclosureOption = $input->getOption('enclosure');
            if ($enclosureOption) {
                $config->setEnclosure($enclosureOption);
            }

            $delimiterOption = $input->getOption('delimiter');
            if ($delimiterOption) {
                $config->setDelimiter($delimiterOption);
            }
            
            $bool = true;
            if ($input->getOption('create-invoices') !== null) {
                $bool = (bool)$input->getOption('create-invoices');
            }
            $config->setCreateInvoice($bool);

            $bool = true;
            if ($input->getOption('create-shipments') !== null) {
                $bool = (bool)$input->getOption('create-shipments');
            }
            $config->setCreateShipment($bool);

            $bool = true;
            $config->setImportOrderNumber($bool);
            
            $bool = true;
            if ($input->getOption('create-credit-memos') !== null) {
                $bool = (bool)$input->getOption('create-credit-memos');
            }
            $config->setCreateCreditMemo($bool);
            
            $errorCount = 5;
            if ($input->getOption('error-limit') !== null) {
                $errorCount = $input->getOption('error-limit');
            }
            $config->setErrorLimit($errorCount);
            

            /** @var \Ulmod\OrderImportExport\Api\ImporterInterface $importModel */
            $importModel = $this->importerFactory->create(['config' => $config]);

            $delimiterConfig = $config->getDelimiter();
            $this->csvProcessor->setDelimiter($delimiterConfig);
            
            $enclosureConfig = $config->getEnclosure();
            $this->csvProcessor->setEnclosure($enclosureConfig);
            
            $this->csvProcessor->setStream($filepath);

            $csvHeaders = [];
            $key = 0;
            while ($row = $this->csvProcessor->streamReadCsv()) {
                if ($key === 0) {
                    $csvHeaders = $row;
                } else {
                    try {
                        $row = array_combine($csvHeaders, $row);
                        $importModel->import($row);
                        $imported++;
                    } catch (ImportException $e) {
                        $errorMsg = $e->getMessage();
                        if ($e->isImported()) {
                            $importError++;
                            $messages['error'][] = 'The row #' . $key . ': ' . $errorMsg;
                            $importMessage = 'The row #' . $key . ': ' . $errorMsg;
                            $importStatus = 1;                              
                        } else {
                            $error++;
                            $messages['error'][] = 'The row #' . $key . ': ' . $errorMsg;
                            $importMessage = 'The row #' . $key . ': ' . $errorMsg;
                            $importStatus = 1;                              
                        }
                    } catch (\Exception $e) {
                        $errorMsg = $e->getMessage();
                        $error++;
                        $messages['error'][] = 'The row #' . $key . ': ' . $errorMsg;
                        $importMessage = 'The row #' . $key . ': ' . $errorMsg;
                        $importStatus = 1;                          
                    }

                    if ($errorCount <= ($error + $importError)) {
                        $messages['error'][] = '';
                        $messages['error'][] = sprintf(
                            'ERROR: Import stopped at row #%d because the error count of %d was reached',
                            $key,
                            $errorCount
                        );
                        $importMessage = 'ERROR: Import stopped at row #%d because the error count of %d was reached';
                        $importStatus = 2;                        
                        break;
                    }
                }
                $key++;
            }

            $this->csvProcessor->unsetStream();

            if ($key < 1) {
                throw new LocalizedException(
                    __('Your file does not contain any orders.')
                );
                $importMessage = 'Your file does not contain any orders.';
                $importStatus = 1;
            }

            // Log import history if enabled
            $importMessage = __('%1 orders imported successfully', $imported);
            $importStatus = 0;           
    
            if ($this->systemConfig->isImportLogEnabled()) {
                $importlogModel->setFilename($fileDir);
                $importlogModel->setFilenameFullpath($importedFileAndSize);
                $importlogModel->setUsername($currentAdminUsername);
                $importlogModel->setStatus($importStatus); // 0 = SUCCESS, 1 = FAILLED, 2 = WARNING
                $importlogModel->setType($importType);                
                $importlogModel->setMessage($importMessage);

                $resultTime = microtime(true) - $startTime;
                $executionTime = gmdate('H:i:s', $resultTime);
                $importlogModel->setExecutionTime($executionTime);
                $importlogModel->save();
            }

        } catch (\Exception $e) {
            $messages['error'][] = $e->getMessage();

            $startTime = microtime(true);
            $this->importLogger->log(
                $startTime,
                $currentAdminUsername,
                $fileDir,
                $executionTime,
                $importType,                
                1,
                $e->getMessage()
            );
        }

        $fileInput = $input->getOption('filename');
        $output->writeln('');
        $output->writeln(sprintf(
            '%d orders imported successfully. File:' . $fileInput . '',
            $imported
        ));
        if ($error) {
            $output->writeln(sprintf(
                'ERROR: %d orders were not imported due to error(s). File:' . $fileInput . '',
                $error
            ));
        }

        if ($importError) {
            $output->writeln(
                sprintf(
                    'ERROR: %d orders were imported but may have experienced error(s) while creating invoices, shipments, or credit memos',
                    $importError
                )
            );
        }

        $output->writeln('');
        $output->writeln("Import finished");
        
        if ($messages['error']) {
            array_unshift(
                $messages['error'],
                'Error Messages:'
            );
            $output->write(
                $messages['error'],
                true
            );
            $output->writeln('');
        }

        if ($messages['success']) {
            array_unshift(
                $messages['success'],
                'Success Messages:'
            );
            $output->write(
                $messages['success'],
                true
            );
        }
    }

    /**
     * Get imported file path
     *
     * @param string $relativeFilePath
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getFilePath($relativeFilePath)
    {
        return $this->varDirectory->getRelativePath($relativeFilePath);
    }

    /**
     * Retrieve imported file size
     *
     * @param string $relativeFilePath
     * @return int|mixed
     */
    public function getReportSize($relativeFilePath)
    {
        return $this->varDirectory->stat($this->getFilePath($relativeFilePath))['size'];
    }      
}
