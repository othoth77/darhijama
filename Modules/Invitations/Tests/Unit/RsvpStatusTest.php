<?php

namespace Modules\Invitations\Tests\Unit;

use Modules\Invitations\Enums\RsvpStatus;
use Tests\TestCase;

class RsvpStatusTest extends TestCase
{
    public function test_present_has_a_success_color_and_french_label(): void
    {
        $this->assertSame('success', RsvpStatus::Present->getColor());
        $this->assertSame('Présent', RsvpStatus::Present->getLabel());
    }

    public function test_absent_has_a_danger_color_and_french_label(): void
    {
        $this->assertSame('danger', RsvpStatus::Absent->getColor());
        $this->assertSame('Absent', RsvpStatus::Absent->getLabel());
    }

    public function test_it_has_exactly_two_cases(): void
    {
        $this->assertCount(2, RsvpStatus::cases());
    }
}
