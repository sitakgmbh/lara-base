<?php

namespace Sitakgmbh\LaraBase\Support;

use Livewire\Component;

class LaraToast
{
    public static function success(string $message, string $title = '', ?Component $component = null): void
    {
        self::flash('success', $message, $title, $component);
    }

    public static function error(string $message, string $title = '', ?Component $component = null): void
    {
        self::flash('error', $message, $title, $component);
    }

    public static function warning(string $message, string $title = '', ?Component $component = null): void
    {
        self::flash('warning', $message, $title, $component);
    }

    public static function info(string $message, string $title = '', ?Component $component = null): void
    {
        self::flash('info', $message, $title, $component);
    }

	private static function flash(string $type, string $message, string $title, ?Component $component = null): void
	{
		if ($component) {
			$component->dispatch('toast', type: $type, message: $message, title: $title);
			\LaraLog::debug("Toast via Livewire dispatch: [{$type}] {$message}");
		} else {
			session()->flash('toast_type',    $type);
			session()->flash('toast_message', $message);
			session()->flash('toast_title',   $title);
			\LaraLog::debug("Toast via Session Flash: [{$type}] {$message}");
		}
	}
}