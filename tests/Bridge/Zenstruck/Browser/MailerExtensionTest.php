<?php

/*
 * This file is part of the zenstruck/mailer-test package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Mailer\Test\Tests\Bridge\Zenstruck\Browser;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Mailer\Test\Bridge\Zenstruck\Browser\MailerExtension;
use Zenstruck\Mailer\Test\Tests\EnvironmentProvider;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @method CustomBrowser browser(array $options = [])
 */
final class MailerExtensionTest extends KernelTestCase
{
    use EnvironmentProvider, HasBrowser;

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_SERVER['KERNEL_BROWSER_CLASS']);
    }

    /**
     * @test
     * @dataProvider environmentProvider
     */
    public function can_use_extension(string $environment): void
    {
        $_SERVER['KERNEL_BROWSER_CLASS'] = CustomBrowser::class;

        $this->browser(['environment' => $environment])
            ->withProfiling()
            ->visit('/no-email')
            ->assertSuccessful()
            ->assertNoEmailSent()
            ->withProfiling()
            ->visit('/send-email')
            ->assertSuccessful()
            ->assertSentEmailCount(1)
            ->assertEmailSentTo('kevin@example.com', 'email subject')
        ;
    }
}

final class CustomBrowser extends KernelBrowser
{
    use MailerExtension;
}
