<?php

namespace RandomItemsBlock\Site\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Laminas\Form\Element\Number;
use Laminas\Form\Element\Select;
use Laminas\View\Renderer\PhpRenderer;

class RandomItems extends AbstractBlockLayout
{
    public function getLabel()
    {
        return 'Random Items'; // @translate
    }

    public function form(PhpRenderer $view, SiteRepresentation $site, SitePageRepresentation $page = null, SitePageBlockRepresentation $block = null)
    {
        $count = new Number('o:block[__blockIndex__][o:data][count]');
        $count->setLabel('Number of items to display'); // @translate
        $count->setAttributes([
            'min' => '1',
            'step' => '1',
        ]);
        $count->setValue($block ? $block->dataValue('count', '3') : '3');

        $totalItems = new Number('o:block[__blockIndex__][o:data][totalItems]');
        $totalItems->setLabel('Max number of random items to choose from)'); // @translate
        $totalItems->setAttributes([
            'min' => '1',
            'step' => '1',
        ]);
        $totalItems->setValue($block ? $block->dataValue('totalItems', '1000') : '1000');

        // get the resource templates options
        // we'll use this to narrow the SQL criteria
        $data['sort_by'] = 'title';
        $response = $view->api()->search('resource_templates', $data);
        $resource_templates = $response->getContent();

        $resource_templates_list = [];
        $resource_templates_list['all'] = "All sites";
        foreach($resource_templates as $res_template) {
            $resource_templates_list[$res_template->id()] = $res_template->label();
        }

        $resTemplateSelectedOption = $block ? $block->dataValue('show_res_template_select_option', '') : '';
        $resTemplateSelect = new Select('o:block[__blockIndex__][o:data][show_res_template_select_option]');
        $resTemplateSelect->setValueOptions($resource_templates_list)->setValue($resTemplateSelectedOption);
        $resTemplateSelect->setLabel('Choose a resource template to select random items from');

        $resTemplateSelect->setValue($block ? $block->dataValue('show_res_template_select_option', '0') : '0');

        $formReturn = $view->formRow($resTemplateSelect);
        $formReturn .= $view->formRow($count);
        $formReturn .= $view->formRow($totalItems);
        return $formReturn;
    }

    public function prepareRender(PhpRenderer $view)
    {
        $view->headScript()->appendFile($view->assetUrl('js/random-items-block.js', 'RandomItemsBlock'));
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        return $view->partial('random-items-block/common/block-layouts/random-items', ['block' => $block, 'view' => $view]);
    }
}
