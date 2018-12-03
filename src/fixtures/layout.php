<?php

    $version = (class_exists('\application\entities\Settings')) ? str_replace('.', '_', \application\entities\Settings::getVersion()) : '1_0';

?>
<!DOCTYPE html>
<html lang="<?php echo \caspar\core\Caspar::getI18n()->getHTMLLanguage(); ?>">
    <head>
        <meta charset="<?php echo \caspar\core\Caspar::getI18n()->getCharset(); ?>">
        <?php \caspar\core\Event::createNew('core', 'header_begins')->trigger(); ?>
        <meta name="author" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="/images/favicon.png">
        <?php

            /* Uncomment the following lines for more favicons
            <link href="/images/favicon.png" rel="apple-touch-icon" />
            <link href="/images/icon-76.png" rel="apple-touch-icon" sizes="76x76" />
            <link href="/images/icon-120.png" rel="apple-touch-icon" sizes="120x120" />
            <link href="/images/icon-152.png" rel="apple-touch-icon" sizes="152x152" />
            <link href="/images/icon-180.png" rel="apple-touch-icon" sizes="180x180" />
            <link href="/images/icon-192.png" rel="icon" sizes="192x192" />
            <link href="/images/icon-128.png" rel="icon" sizes="128x128" />
            */

        ?>
        <title><?php echo strip_tags($csp_response->getTitle()); ?></title>
        <?php foreach ($csp_response->getStylesheets() as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>?v=<?= $version; ?>">
        <?php endforeach; ?>

        <?php foreach ($csp_response->getJavascripts() as $js): ?>
            <script type="text/javascript" src="<?php echo $js; ?>?v=<?= $version; ?>"></script>
        <?php endforeach; ?>

        <?php \caspar\core\Event::createNew('core', 'header_ends')->trigger(); ?>
    </head>
    <body>
        <?php echo $content; ?>
    </body>
</html>