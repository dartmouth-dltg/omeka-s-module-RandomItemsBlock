<?php

namespace RandomItemsBlock\Site\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Laminas\Form\Element\Number;
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
        $count->setLabel('Number of items'); // @translate
        $count->setAttributes([
            'min' => '1',
            'step' => '1',
        ]);
        $count->setValue($block ? $block->dataValue('count', '3') : '3');

        return $view->formRow($count);
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {
        return $view->partial('random-items-block/common/block-layouts/random-items', ['block' => $block]);
    }
}
