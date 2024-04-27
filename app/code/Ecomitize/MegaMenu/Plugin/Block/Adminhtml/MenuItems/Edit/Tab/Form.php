<?php

namespace Ecomitize\MegaMenu\Plugin\Block\Adminhtml\MenuItems\Edit\Tab;

/**
 *
 */
class Form
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
    )
    {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param \Sm\MegaMenu\Block\Adminhtml\MenuItems\Edit\Tab\Form $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        \Sm\MegaMenu\Block\Adminhtml\MenuItems\Edit\Tab\Form $subject,
        \Closure $proceed
    )
    {
        $form = $subject->getForm();
        $modelItem = $this->coreRegistry->registry('megamenu_menuitems');
        if (is_object($form) && $modelItem && $modelItem->getId()) {
            $fieldset = $form->getElement('base_fieldset');
            $fieldset->addField(
                'url',
                'text',
                [
                    'name' => 'url',
                    'label' => __('Url'),
                    'title' => __('Url'),
                    'value' => $modelItem->getData('url')
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();
    }
}
