<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ulmod\OrderImportExport\Console\Command;

use Ulmod\OrderImportExport\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ulmod\OrderImportExport\Api\ExporterInterfaceFactory;
use Ulmod\OrderImportExport\Api\Data\ExportConfigInterfaceFactory;
use Magento\Framework\App\State as AppState;
use Ulmod\OrderImportExport\Model\ExportlogFactory;
use Ulmod\OrderImportExport\Model\Logger\ExportLogger;
use Ulmod\OrderImportExport\Model\Config as SystemConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
        
class Export extends AbstractCommand
{
    /**
     * Command name for cli
     */
    const COMMAND_NAME = 'ulmod:order:export';

    /**
     * Command description in cli
     */
    const COMMAND_DESCRIPTION = 'Ulmod Order Export to CSV';
    
    /**
     * @var ExporterInterfaceFactory
     */
    private $modelFactory;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var ExportConfigInterfaceFactory
     */
    private $configFactory;

    /**
     * @var ExportlogFactory
     */
    protected $exportlogFactory;

    /**
     * @var ExportLogger
     */
    protected $exportLogger;   
  
    /**
     * @var SystemConfig
     */
    protected $systemConfig;   

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @param ExporterInterfaceFactory $modelfactory
     * @param ExportConfigInterfaceFactory $configFactory
     * @param AppState $state
     * @param array $defaultOptions
     * @param null|string $name
     */
    public function __construct(
        ExporterInterfaceFactory $modelfactory,
        ExportConfigInterfaceFactory $configFactory,
        AppState $state,
        ExportlogFactory $exportlogFactory,
        ExportLogger $exportLogger,
        SystemConfig $systemConfig,
        Filesystem $filesystem,                
        $defaultOptions = [],
        $name = null
    ) {
        parent::__construct($defaultOptions, $name);
        $this->modelFactory  = $modelfactory;
        $this->configFactory = $configFactory;
        $this->state         = $state;
        $this->exportlogFactory = $exportlogFactory;
        $this->exportLogger = $exportLogger;
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
                'directory',
                null,
                InputOption::VALUE_OPTIONAL,
                'Export directory, relative to Magento 2 root. Eg. --directory=var/ulmod_orderexport/ .Default output directory is ' .
                $this->getDefaultOption('directory')
            ),
            new InputOption(
                'filename',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filename output. Eg. --filename=export-orders-yyyy-mm-dd.csv .Default filename is ' . $this->getDefaultOption('filename')
            ),
            new InputOption(
                'from',
                null,
                InputOption::VALUE_OPTIONAL,
                'Date from in yyyy-mm-dd format. Eg. --from=yyyy-mm-dd'
            ),
            new InputOption(
                'to',
                null,
                InputOption::VALUE_OPTIONAL,
                'Date to in yyyy-mm-dd format. Eg. --to=yyyy-mm-dd'
            ),
            new InputOption(
                'enclosure',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV field enclosure. Eg. --enclosure=" .Default field enclosure is ' . $this->getDefaultOption('enclosure')
            ),
            new InputOption(
                'delimiter',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV field separator. Eg. --delimiter=, .Default field separator is ' . $this->getDefaultOption('delimiter')
            ),
        ];
        
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->setDefinition($options);
            
        parent::configure();
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var \Ulmod\OrderImportExport\Model\Exportlog $exportlogModel */
        $exportlogModel = $this->exportlogFactory->create();        
      
        $exportType = 2;          
        $executionTime = '00:00:00';
        $currentAdminUsername = 'No';

        $fileName = '';      
        if ($input->getOption('filename')) {
            $fileName = $input->getOption('filename');
        } else {
            $fileName = $this->getDefaultOption('filename');           
        }

        $dirPath = '';      
        if ($input->getOption('directory')) {
            $dirPath = $input->getOption('directory');
        } else {
            $dirPath = $this->getDefaultOption('directory');           
        }        

        try {
            $this->state->setAreaCode(
                \Magento\Framework\App\Area::AREA_ADMINHTML
            );
        } catch (\Exception $e) {
        }

        try {
            // set created log date here to diff excecution time
            $startTime = microtime(true);
            if ($this->systemConfig->isExportLogEnabled()) {
                $exportlogModel->setCreatedAt($startTime);
            }

            /** @var \Ulmod\OrderImportExport\Api\Data\ExportConfigInterface $config */
            $config = $this->configFactory->create();
            
            $config->setEnclosure($this->getDefaultOption('enclosure'));
            if ($input->getOption('enclosure')) {
                $config->setEnclosure($input->getOption('enclosure'));
            }
            
            $config->setDelimiter($this->getDefaultOption('delimiter'));
            if ($input->getOption('delimiter')) {
                $config->setDelimiter($input->getOption('delimiter'));
            }
            
            $config->setFilename($this->getDefaultOption('filename'));
            if ($input->getOption('filename')) {
                $config->setFilename($input->getOption('filename'));
            }

            $config->setDirectory($this->getDefaultOption('directory'));
            if ($input->getOption('directory')) {
                $config->setDirectory($input->getOption('directory'));
            }
            
            $optionFrom = $input->getOption('from');
            $config->setFrom($optionFrom);
            
            $optionTo = $input->getOption('to');
            $config->setTo($optionTo);

            /** @var \Ulmod\OrderImportExport\Api\ExporterInterface $exporter */
            $exporter = $this->modelFactory->create(['config' => $config]);
            
            $result   = $exporter->export(true);           
            
            $output->writeln("SUCCESS - Orders Exported Successfully");
      

            $exportMessage = 'All orders have been exported successfully';        

            $relativeFilePath = $dirPath . $fileName;
            $exportedFileSize = $this->getReportSize(
                $relativeFilePath
            );
            $exportedFileSizeKb = round($exportedFileSize / 1024, 2) . 'KB';
            $exportedFileAndSize = $relativeFilePath .' | ' . $exportedFileSizeKb;
            
            // Log export history if enabled
            if ($this->systemConfig->isExportLogEnabled()) {
                $exportlogModel->setUsername($currentAdminUsername);
                $exportlogModel->setFilename($fileName);
                $exportlogModel->setFilenameFullpath($exportedFileAndSize);
                $exportlogModel->setStatus(0); // 0 = SUCCESS, 1 = FAILLED
                $exportlogModel->setType($exportType);              
                $exportlogModel->setMessage($exportMessage);
                
                $resultTime = microtime(true) - $startTime;
                $executionTime = gmdate('H:i:s', $resultTime);
                $exportlogModel->setExecutionTime($executionTime);
                
                $exportlogModel->save();
            }            
        } catch (\Exception $e) {
            $result = $e->getMessage();
            $output->writeln("ERROR - The export did not go well");

            $startTime = microtime(true);            
            $this->exportLogger->log(
                $startTime,
                $currentAdminUsername,
                $fileName,
                $executionTime,
                $exportType,                
                1,
                $e->getMessage()
            );            
        }
        $output->writeln($result);
    } 

    /**
     * Get exported file path
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
     * Retrieve exported file size
     *
     * @param string $relativeFilePath
     * @return int|mixed
     */
    public function getReportSize($relativeFilePath)
    {
        return $this->varDirectory->stat($this->getFilePath($relativeFilePath))['size'];
    }  
}
