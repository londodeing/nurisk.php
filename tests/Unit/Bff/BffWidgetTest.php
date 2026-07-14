<?php

namespace Tests\Unit\Bff;

use App\Http\Controllers\Api\Bff\Widgets\SummaryCardWidget;
use App\Http\Controllers\Api\Bff\Widgets\ActionListWidget;
use App\Http\Controllers\Api\Bff\Widgets\DocumentQueueWidget;
use App\Http\Controllers\Api\Bff\Widgets\HeaderBannerWidget;
use App\Http\Controllers\Api\Bff\Widgets\EmptyStateWidget;
use PHPUnit\Framework\TestCase;

class BffWidgetTest extends TestCase
{
    public function test_summary_card_generates_correct_json_structure()
    {
        $widget = SummaryCardWidget::make('Title', '10', 'icon', '#FFF');
        $array = $widget->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertStringStartsWith('wdg_', $array['id']);
        $this->assertEquals('SummaryCard', $array['type']);
        $this->assertEquals('Title', $array['props']->title);
        $this->assertEquals('10', $array['props']->value);
    }

    public function test_action_list_generates_correct_json_structure()
    {
        $widget = ActionListWidget::make('Menu', [['id' => '1', 'label' => 'A']]);
        $array = $widget->toArray();

        $this->assertEquals('ActionList', $array['type']);
        $this->assertEquals('Menu', $array['props']->title);
        $this->assertCount(1, $array['props']->items);
    }

    public function test_document_queue_generates_correct_json_structure()
    {
        $widget = DocumentQueueWidget::make('Docs', [['id' => 1, 'title' => 'Doc 1']])
            ->setAction('on_approve', ['type' => 'api_call']);
        $array = $widget->toArray();

        $this->assertEquals('DocumentQueue', $array['type']);
        $this->assertObjectHasProperty('on_approve', $array['actions']);
        $this->assertEquals('api_call', $array['actions']->on_approve['type']);
    }

    public function test_header_banner_generates_correct_json_structure()
    {
        $widget = HeaderBannerWidget::make('Warning', 'danger');
        $array = $widget->toArray();

        $this->assertEquals('HeaderBanner', $array['type']);
        $this->assertEquals('Warning', $array['props']->message);
        $this->assertEquals('danger', $array['props']->level);
    }

    public function test_empty_state_generates_correct_json_structure()
    {
        $widget = EmptyStateWidget::make('Kosong', 'Tidak ada data');
        $array = $widget->toArray();

        $this->assertEquals('EmptyState', $array['type']);
        $this->assertEquals('Kosong', $array['props']->title);
        $this->assertEquals('Tidak ada data', $array['props']->message);
        $this->assertEquals('inbox', $array['props']->icon);
    }
}
