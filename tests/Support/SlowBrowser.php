<?php

namespace Tests\Support;

use Laravel\Dusk\Browser;

/**
 * Custom Dusk Browser yang menambahkan efek Slow Motion (slow-mo)
 * agar setiap aksi (ketik, klik, pilih dropdown, navigasi, verifikasi) terlihat jelas dan santai di layar.
 */
class SlowBrowser extends Browser
{
    /**
     * Berikan jeda waktu (slow-mo) sesuai konfigurasi DUSK_SLOW_MO (default 700ms).
     */
    protected function slowMo(?int $customMs = null): void
    {
        $delay = $customMs ?? (int) (env('DUSK_SLOW_MO', 700));
        if ($delay > 0) {
            $this->pause($delay);
        }
    }

    public function visit($url)
    {
        $result = parent::visit($url);
        $this->slowMo();
        return $result;
    }

    public function visitRoute($route, $parameters = [])
    {
        $result = parent::visitRoute($route, $parameters);
        $this->slowMo();
        return $result;
    }

    public function type($field, $value)
    {
        $charDelay = (int) env('DUSK_TYPE_DELAY', 30);
        if ($charDelay > 0) {
            $this->typeSlowly($field, $value, $charDelay);
        } else {
            parent::type($field, $value);
        }

        $this->slowMo();
        return $this;
    }

    public function clear($field)
    {
        $result = parent::clear($field);
        $this->slowMo(300);
        return $result;
    }

    public function click($selector = null)
    {
        $this->slowMo(400);
        $result = parent::click($selector);
        $this->slowMo();
        return $result;
    }

    public function clickLink($link, $element = 'a')
    {
        $this->slowMo(400);
        $result = parent::clickLink($link, $element);
        $this->slowMo();
        return $result;
    }

    public function press($button)
    {
        $this->slowMo(400);
        $result = parent::press($button);
        $this->slowMo();
        return $result;
    }

    public function select($field, $value = null)
    {
        $this->slowMo(300);
        $result = parent::select($field, $value);
        $this->slowMo();
        return $result;
    }

    public function check($field, $value = null)
    {
        $this->slowMo(300);
        $result = parent::check($field, $value);
        $this->slowMo();
        return $result;
    }

    public function uncheck($field, $value = null)
    {
        $this->slowMo(300);
        $result = parent::uncheck($field, $value);
        $this->slowMo();
        return $result;
    }

    public function waitFor($selector, $seconds = null)
    {
        $result = parent::waitFor($selector, $seconds);
        $this->slowMo(500);
        return $result;
    }

    public function waitForText($text, $seconds = null, $ignoreCase = false)
    {
        $result = parent::waitForText($text, $seconds, $ignoreCase);
        $this->slowMo(500);
        return $result;
    }

    public function waitForLocation($path, $seconds = null)
    {
        $result = parent::waitForLocation($path, $seconds);
        $this->slowMo(500);
        return $result;
    }

    public function assertSee($text, $ignoreCase = false)
    {
        $result = parent::assertSee($text, $ignoreCase);
        $this->slowMo(400);
        return $result;
    }

    public function assertDontSee($text, $ignoreCase = false)
    {
        $result = parent::assertDontSee($text, $ignoreCase);
        $this->slowMo(400);
        return $result;
    }

    public function assertPathIs($path)
    {
        $result = parent::assertPathIs($path);
        $this->slowMo(400);
        return $result;
    }
}
