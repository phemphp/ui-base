<?php

declare(strict_types=1);

namespace App\Ui;

use Phem\Html;
use Phiki\Phiki;
use Phiki\Theme\Theme;

/**
 * Colors the code of a `.phem` file: PHP, the markup and its Twig. It uses the grammar of the
 * editor plugins, as the docs website does, so the demo shows the same colors as the editors.
 * The grammar is a copy of `editors/vscode/syntaxes/phem.tmLanguage.json` of phem: the phem/phem
 * package does not have it. Copy it again after a change of the grammar.
 */
final class Highlighter
{
    private const string GRAMMAR = '/etc/phem.tmLanguage.json';

    private static ?Phiki $phiki = null;

    /** A `<pre>` with the colored code: GitHub Light, and GitHub Dark in the dark mode (app.css). */
    public static function phem(string $code): Html
    {
        self::$phiki ??= (new Phiki())->grammar('phem', \dirname(__DIR__, 2).self::GRAMMAR);

        return new Html(self::$phiki->codeToHtml($code, 'phem', ['light' => Theme::GithubLight, 'dark' => Theme::GithubDark])->toString());
    }
}
