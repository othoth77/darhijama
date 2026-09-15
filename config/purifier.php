<?php

/**
 * Only the "dar_hijama_article" profile below is used by this application
 * (Article::purifiedContent(), see Applications/DarHijama/Domain/Article.php).
 * Its allowlist matches exactly what Filament's RichEditor toolbar can
 * produce for an article body (h2/h3, bold/italic, lists, links,
 * blockquote, images) — see Applications/DarHijama/Filament/Resources/ArticleResource.php
 * for the toolbar configuration these two lists are kept in sync with.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */
return [
    'encoding' => 'UTF-8',
    'finalize' => true,
    'ignoreNonStrings' => false,
    'cachePath' => storage_path('app/purifier'),
    'cacheFileMode' => 0755,
    'settings' => [
        'default' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'p,b,strong,i,em,ul,ol,li,a[href|title],br',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ],

        'dar_hijama_article' => [
            'HTML.Doctype' => 'HTML 4.01 Transitional',
            'HTML.Allowed' => 'h2,h3,p,b,strong,i,em,u,ul,ol,li,a[href|title|rel|target],blockquote,img[src|alt|width|height],br',
            'CSS.AllowedProperties' => '',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
            'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true],
        ],
    ],
];
