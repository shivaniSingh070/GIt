<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Console\Command;

use Ulmod\OrderImportExport\Exception\ImportException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
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

class ImportMass extends \Symfony\Component\Console\Command\Command
{
    /**
     * Command name for cli
     */
    const COMMAND_NAME = 'ulmod:order:import-multi';

    /**
     * Command description in cli
     */
    const COMMAND_DESCRIPTION = 'Ulmod Order Import to CSV (Multiple/Bulk CSV file import)';
    
    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $directory;
    
    /**
     * @var FileModel
     */
    private $fileModel;

    /**
     * @var Filesystem
     */
    protected $filesystem;
    
    /**
     * @var CsvProcessor
     */
    private $csvProcessor;

    /**
     * @var ImportConfigInterfaceFactory
     */
    private $configFactory;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var ImporterInterfaceFactory
     */
    private $importerFactory;

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
     * @param Filesystem $filesystem
     * @param null|string $name
     */
    public function __construct(
        FileModel $fileModel,
        CsvProcessor $csvProcessor,
        ImporterInterfaceFactory $importerFactory,
        ImportConfigInterfaceFactory $configFactory,
        AppState $state,
        Filesystem $filesystem,
        ImportlogFactory $importlogFactory,
        ImportLogger $importLogger,
        SystemConfig $systemConfig,        
        $name = null
    ) {
        parent::__construct($name);
        
        $this->fileModel = $fileModel;
        $this->csvProcessor    = $csvProcessor;
        $this->importerFactory = $importerFactory;
        $this->configFactory   = $configFactory;
        $this->state = $state;
        $this->filesystem = $filesystem;    
        $this->importlogFactory = $importlogFactory;
        $this->importLogger = $importLogger;
        $this->systemConfig = $systemConfig;             
        $this->directory = $this->filesystem
            ->getDirectoryRead(DirectoryList::ROOT);
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);  

    }

    /**
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                'dir',
                null,
                InputOption::VALUE_OPTIONAL,
                'Directory of multiple/bulk CSV files. Eg. --dir=var/ulmod_orderimport'
            ),
            new InputOption(
                'filename',
                null,
                InputOption::VALUE_REQUIRED,
                'Relative filepath to file being imported. Eg. --filename=var/ulmod_orderimport/import-orders-yyyy-mm-dd.csv'
            ),
            new InputOption(
                'enclosure',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV field enclosure. Eg. --enclosure="'
            ),
            new InputOption(
                'delimiter',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV field separator. Eg. --delimiter=,'
            ),
            new InputOption(
                'create-invoices',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enter 1 for YES and 0 for NO. Eg. --create-invoices=1'
            ),
            new InputOption(
                'create-credit-memos',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enter 1 for YES and 0 for NO. Eg. --create-credit-memos=1'
            ),
            new InputOption(
                'create-shipments',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enter 1 YES and 0 for NO. Eg. --create-shipments=1'
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
     * @param $i
     * @param $total
     *
     * @return bool|float
     */
    protected function getPercentForCurrentProgress($i, $total)
    {
        if ($i > $total) {
            return false;
        }

        $percent = ($i / $total) * 100;
        return round($percent, 2);
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Ulmod\OrderImportExport\Model\Importlog $importlogModel */
        $importlogModel = $this->importlogFactory->create();        
      
        $importType = 3;          
        $executionTime = '00:00:00';
        $currentAdminUsername = 'No';

        $fileDir = 'Check at the imported directory : ' . $input->getOption('dir');      
        $importedFileAndSize = 'Check at the imported directory : ' . $input->getOption('dir');

        $messages = [
            'error'   => [],
            'success' => []
        ];

        $imported = 0;
        $importError = 0;
        $error = 0;
        
        try {
            $this->state->setAreaCode(
                AppArea::AREA_ADMINHTML
            );
        } catch (\Exception $e) {

        }


        // start multi import
        $directory = $input->getOption('dir');
        $isDir = $this->directory->isDirectory($directory);

        if (false === $isDir) {
            $output->writeln('Directory not exist!');
            $fileDir = 'File(s) do not exist on specified directory!';    
            return;
        }
        
        $output->writeln("Import started");

        $i = 0;
        $csvFiles = $this->directory->read($directory);
        $csvFilesCount = count($csvFiles);
        foreach ($csvFiles as $csvFile) {
            $i++;
            $currentDateTime = date("Y-m-d H:i:s");
            $currentProgress = $this->getPercentForCurrentProgress($i, $csvFilesCount);
            $output->writeln("# {$i}/{$csvFilesCount} ({$currentProgress}%) - [{$currentDateTime}] - {$csvFile}");
            $output->writeln("-----------------------------------------------");
                
            try {

                // set created log date here to diff excecution time
                $startTime = microtime(true);
                if ($this->systemConfig->isImportLogEnabled()) {
                    $importlogModel->setCreatedAt($startTime);
                }

                if (!$csvFile) {
                    throw new LocalizedException(
                        __('You must enter the filepath to import')
                    );
                }

                $filepath = $this->fileModel->getAbsolutePath($csvFile);

                $isFileExists = $this->fileModel->fileExists($filepath);
                if (!$isFileExists) {
                    throw new LocalizedException(
                        __('%1 does not exist on the server', $filepath)
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

                    
                /** @var \Ulmod\OrderImportExport\Api\ImporterInterface importModel */
                $importModel = $this->importerFactory->create(['config' => $config]);
        
                $enclosureConfig = $config->getEnclosure();
                $this->csvProcessor->setEnclosure($enclosureConfig);

                $delimiterConfig = $config->getDelimiter();
                $this->csvProcessor->setDelimiter($delimiterConfig);
                    
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
                            $error++;
                            $errorMsg = $e->getMessage();
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
                    
                $output->writeln("----------------------------------------------");
                    
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
        }

        $fileInput = $input->getOption('filename');
        $output->writeln('');
        $output->writeln(sprintf(
            '%d orders imported successfully',
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
            array_unshift($messages['error'], 'Error Messages:');
            $output->write($messages['error'], true);
            $output->writeln('');
        }

        if ($messages['success']) {
            array_unshift($messages['success'], 'Success Messages:');
            $output->write($messages['success'], true);
        }
    }    
}
