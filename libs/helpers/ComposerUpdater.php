<?php

namespace WebCMS;

use Composer\Script\Event;

class ComposerUpdater {
	
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        
		print_r($composer);
    }

    public static function postPackageInstall(Event $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        // do stuff
    }

    public static function warmCache(Event $event)
    {
        // make cache toasty
    }
}