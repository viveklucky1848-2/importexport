<?php
/**
 * @copyright: Copyright © 2015 Firebear Studio. All rights reserved.
 * @author: Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Plugin\Block\Import;

/**
 * Class Form
 * @package Firebear\ImportExport\Plugin\Block\Import
 */
class Form {

    /**
     * @var \Firebear\ImportExport\Model\Source\ConfigInterface|null
     */
    protected $_config = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Firebear\ImportExport\Model\Source\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        \Firebear\ImportExport\Model\Source\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_config = $config;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Add import source fieldset to default import form
     *
     * @param \Magento\ImportExport\Block\Adminhtml\Import\Edit\Form $subject
     * @param                                                        $form
     * @return array
     */
    public function beforeSetForm(\Magento\ImportExport\Block\Adminhtml\Import\Edit\Form $subject, $form)
    {
        $fileFieldset = $form->getElement('upload_file_fieldset');
        $oldClass = $fileFieldset->getClass();
        $fileFieldset->setClass('source-fieldset ' . $oldClass);

        $types = $this->_config->get();
        $sources = [
            ['label' => __('-- Please Select --'), 'value' => ''],
            ['label' => __('File'), 'value' => 'file']
        ];
        foreach ($types as $typeName => $type) {
            $sources[] = ['label' => $type['label'], 'value' => $typeName];
        }


        $fieldsets['source'] = $form->addFieldset(
            'import_source_fieldset',
            ['legend' => __('Import Source'), 'class' => 'no-display'],
            'custom_behavior_fieldset'
        );

        $fieldsets['source']->addField(
            'import_source',
            'select',
            [
                'name' => 'import_source',
                'label' => __('Source'),
                'title' => __('Source'),
                'required' => true,
                'class' => 'input-text',
                'onchange' => 'varienImport.handleImportSourceSelector();',
                'values' => $sources,
            ]
        );

        foreach ($types as $typeName => $type) {
            $fieldsets[$typeName] = $form->addFieldset(
                'upload_' . $typeName . '_fieldset',
                ['legend' => __($type['label']), 'class' => 'source-fieldset no-display']
            );


            foreach ($type['fields'] as $fieldName => $field) {
                if ($fieldName != 'file_path') {
                    continue;
                }

                $fieldsets[$typeName]->addField(
                    $typeName . '_' . $fieldName,
                    $field['type'],
                    [
                        'name' => $typeName . '_' . $fieldName,
                        'label' => __($field['label']),
                        'title' => __($field['label']),
                        'required' => $field['required'],
                        'class' => 'input-' . $field['type']
                    ]
                );
            }
        }

        return [$form];
    }
}