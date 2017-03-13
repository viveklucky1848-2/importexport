<?php
/**
 * @copyright: Copyright © 2015 Firebear Studio. All rights reserved.
 * @author: Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Source\Type;

class Ftp extends AbstractType
{
    /**
     * @var string
     */
    protected $_code = 'ftp';

    /**
     * Download remote source file to temporary directory
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadSource()
    {
        if ($client = $this->_getSourceClient()) {
            $sourceFilePath = $this->getData($this->_code . '_file_path');
            $fileName = basename($sourceFilePath);
            $filePath = $this->_directory->getAbsolutePath($this->getImportVarPath() . '/' . $fileName);

            $filesystem = new \Magento\Framework\Filesystem\Io\File();
            $filesystem->setAllowCreateFolders(true);
            $filesystem->checkAndCreateFolder($this->_directory->getAbsolutePath($this->getImportVarPath()));

            $result = $client->read($sourceFilePath, $filePath);

            if ($result) {
                return $this->_directory->getAbsolutePath($this->getImportPath() . '/' . $fileName);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("File not found"));
            }
        } else {
            throw new  \Magento\Framework\Exception\LocalizedException(__("Can't initialize %s client", $this->_code));
        }
    }

    /**
     * Download remote images to temporary media directory
     *
     * @param $importImage
     * @param $imageSting
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importImage($importImage, $imageSting)
    {
        if ($client = $this->_getSourceClient()) {
            $sourceFilePath = $this->getData($this->_code . '_file_path');
            $sourceDirName = dirname($sourceFilePath);
            $filePath = $this->_directory->getAbsolutePath($this->getMediaImportPath() . $imageSting);
            $dirname = dirname($filePath);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0775, true);
            }
            if ($filePath) {
                $result = $client->read($sourceDirName . '/' . $importImage, $filePath);
            }
        }
    }

    protected function _getSourceClient()
    {
        if (!$this->getClient()) {
            if (
                $this->getData('host')
                && $this->getData('port')
                && $this->getData('user')
                && $this->getData('password')
            ) {
                $settings = $this->getData();
            } else {
                $settings = $this->_scopeConfig->getValue(
                    'firebear_importexport/ftp',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }

            $settings['passive'] = true;
            try {
                $connection = new \Firebear\ImportExport\Model\Filesystem\Io\Ftp();
                $connection->open(
                    $settings
                );
                $this->_client = $connection;
            } catch(\Exception $e){
                throw new  \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }

        }

        return $this->getClient();
    }
}