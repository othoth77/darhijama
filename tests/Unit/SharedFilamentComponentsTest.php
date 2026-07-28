<?php

namespace Tests\Unit;

use App\Filament\Components\MediaUploadField;
use App\Filament\Components\SharedActions;
use App\Filament\Components\SharedTableColumns;
use App\Filament\Components\StatusComponents;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\FileUpload;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Orders\Enums\OrderStatus;
use Tests\TestCase;

class SharedFilamentComponentsTest extends TestCase
{
    public function test_status_field_column_and_filter_share_enum_options(): void
    {
        $field = StatusComponents::field(OrderStatus::class, OrderStatus::Nouveau);
        $column = StatusComponents::column();
        $filter = StatusComponents::filter(InvitationStatus::class);

        $this->assertSame('status', $field->getName());
        $this->assertSame(OrderStatus::Nouveau->value, $field->getDefaultState());
        $this->assertSame('status', $column->getName());
        $this->assertSame('status', $filter->getName());
    }

    public function test_shared_actions_include_confirmation_and_restore_foundations(): void
    {
        $publish = SharedActions::publish(fn () => null);
        $archive = SharedActions::archive(fn () => null);
        $restore = SharedActions::restore();
        $copy = SharedActions::copyPublicLink(fn () => 'https://example.test/public');

        $this->assertSame('publish', $publish->getName());
        $this->assertTrue($publish->shouldOpenModal());
        $this->assertSame('archive', $archive->getName());
        $this->assertTrue($archive->shouldOpenModal());
        $this->assertInstanceOf(RestoreAction::class, $restore);
        $this->assertTrue($restore->shouldOpenModal());
        $this->assertSame('copyPublicLink', $copy->getName());
    }

    public function test_shared_media_upload_and_table_columns_keep_project_defaults(): void
    {
        $upload = MediaUploadField::image('preview', 'templates/previews', 'Aperçu');
        $createdAt = SharedTableColumns::createdAt();
        $publishedAt = SharedTableColumns::publishedAt();

        $this->assertInstanceOf(FileUpload::class, $upload);
        $this->assertSame('preview', $upload->getName());
        $this->assertSame('created_at', $createdAt->getName());
        $this->assertSame('published_at', $publishedAt->getName());
    }
}
