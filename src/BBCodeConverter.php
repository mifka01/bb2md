<?php

/**
 * @file BBCodeConverter.php
 *
 * @brief This file contains the BBCodeConverter class.
 *
 * @details
 *
 * @author Filippo F. Fadda, Radim Mifka
 *
 * @licence Apache-2.0 license
 */

namespace mifka01\bb2md;

/**
 * @brief A rudimental converter that takes as input a BBCode formatted text and converts it to Markdown.
 */
class BBCodeConverter
{
    protected $text;
    protected $lang;

    public function __construct($lang = 'en')
    {
        $this->lang = $lang;
    }

    /**
     * @brief Removes BBCode size.
     */
    protected function removeSize()
    {
        $this->text = preg_replace_callback(
            '%\[size=\d*\]([\W\D\w\s]*?)\[/size\]%iu',
            function ($matches) {
                return $matches[1];
            },
            $this->text
        );
    }

    /**
     * @brief Removes BBCode center.
     */
    protected function removeCenter()
    {
        $this->text = preg_replace_callback(
            '%\[center\]([\W\D\w\s]*?)\[/center\]%iu',
            function ($matches) {
                return $matches[1];
            },
            $this->text
        );
    }

    /**
     * @brief Removes BBCode center.
     */
    protected function removeLeft()
    {
        $this->text = preg_replace_callback(
            '%\[left\]([\W\D\w\s]*?)\[/left\]%iu',
            function ($matches) {
                return $matches[1];
            },
            $this->text
        );
    }

    /**
     * @brief Removes BBCode color.
     */
    protected function removeColor()
    {
        $this->text = preg_replace_callback(
            '%\[color=\w*\]([\W\D\w\s]*?)\[/color\]%iu',
            function ($matches) {
                return $matches[1];
            },
            $this->text
        );
    }

    /**
     * @brief Replaces BBCode bold.
     */
    protected function replaceBold()
    {
        $this->text = preg_replace_callback(
            '%\[b\]\s*\[\/b\]%iu',
            function ($matches) {
                return '';
            },
            $this->text
        );

        do {
            $this->text = preg_replace_callback(
                '%\[b\](?:(?!\[b\]|\[\/b\]).)*\[\/b\]%iu',
                function ($matches) {
                    $content = preg_replace('/^\[b\]|\[\/b\]$/', '', $matches[0]);

                    return '**' . trim($content, ' ') . '**';
                },
                $this->text,
                -1,
                $count
            );
        } while ($count > 0);
    }

    protected function replaceItalic()
    {
        $this->text = preg_replace_callback(
            '%\[i\]\s*\[\/i\]%iu',
            function ($matches) {
                return '';
            },
            $this->text
        );

        do {
            $this->text = preg_replace_callback(
                '%\[i\](?:(?!\[i\]|\[\/i\]).)*\[\/i\]%iu',
                function ($matches) {
                    $content = preg_replace('/^\[i\]|\[\/i\]$/', '', $matches[0]);

                    return '*' . trim($content, ' ') . '*';
                },
                $this->text,
                -1,
                $count
            );
        } while ($count > 0);
    }

    /**
     * @brief Replaces BBCode underline. Hoedown support underline.
     */
    protected function replaceUnderline()
    {
        $this->text = preg_replace_callback(
            '%\[u\]\s*\[\/u\]%iu',
            function ($matches) {
                return '';
            },
            $this->text
        );

        do {
            $this->text = preg_replace_callback(
                '%\[u\](?:(?!\[u\]|\[\/u\]).)*\[\/u\]%iu',
                function ($matches) {
                    $content = preg_replace('/^\[u\]|\[\/u\]$/', '', $matches[0]);

                    return '_' . trim($content, ' ') . '_';
                },
                $this->text,
                -1,
                $count
            );
        } while ($count > 0);
    }

    /**
     * @brief Replaces BBCode strikethrough.
     */
    protected function replaceStrikethrough()
    {
        $this->text = preg_replace_callback(
            '%\[s\]\s*\[\/s\]%iu',
            function ($matches) {
                return '';
            },
            $this->text
        );

        do {
            $this->text = preg_replace_callback(
                '%\[s\](?:(?!\[s\]|\[\/s\]).)*\[\/s\]%iu',
                function ($matches) {
                    $content = preg_replace('/^\[s\]|\[\/s\]$/', '', $matches[0]);

                    return '~~' . trim($content, ' ') . '~~';
                },
                $this->text,
                -1,
                $count
            );
        } while ($count > 0);
    }

    /**
     * @brief Replaces BBCode lists.
     */
    protected function replaceLists()
    {
        $this->text = preg_replace_callback(
            '%\[list(?P<type>=1)?\](?P<items>[\W\D\w\s]*?)\[/list\]%iu',
            function ($matches) {
                $buffer = '';

                $list = preg_replace('/\s*$|^\s*/mu', '', $matches['items']);
                if (is_null($list)) {
                    throw new \RuntimeException(sprintf("Text identified by '%d' has malformed BBCode lists", $this->id));
                }

                $items = preg_split('/\[\*\]/u', $list);

                $counter = count($items);

                if (isset($matches['type']) && '=1' == $matches['type']) { // ordered list
                    // We start from 1 to discard the first string, in fact, it's empty.
                    for ($i = 1; $i < $counter; ++$i) {
                        if (!empty($items[$i])) {
                            $buffer .= (string) $i . '. ' . trim($items[$i]) . PHP_EOL;
                        }
                    }
                } else { // unordered list
                    // We start from 1 to discard the first string, in fact, it's empty.
                    for ($i = 1; $i < $counter; ++$i) {
                        if (!empty($items[$i])) {
                            $buffer .= '- ' . trim($items[$i]) . PHP_EOL;
                        }
                    }
                }

                // We need a like break above the list and another one below.
                if (!empty($buffer)) {
                    $buffer = PHP_EOL . $buffer . PHP_EOL;
                }

                return $buffer;
            },
            $this->text
        );
    }

    /**
     * @brief Replaces BBCode urls.
     */
    protected function replaceUrls()
    {
        $this->text = preg_replace_callback(
            '%\[url\s*=\s*("(?:[^"]*")|\A[^\']*\Z|(?:[^\'">\]\s]+))\s*(?:[^]\s]*)\]([\W\D\w\s]*?)\[/url\]%iu',
            function ($matches) {
                if (isset($matches[1]) && isset($matches[2])) {
                    return '[' . $matches[2] . '](' . $matches[1] . ')';
                } else {
                    throw new \RuntimeException(sprintf("Text identified by '%d' has malformed BBCode urls", $this->id));
                }
            },
            $this->text
        );
    }

    /**
     * @brief Replaces BBCode urls without a description.
     */
    protected function replaceUndescribedUrls()
    {
        $this->text = preg_replace_callback(
            '%\[url\]([\W\D\w\s]*?)\[/url\]%iu',
            function ($matches) {
                if (isset($matches[1])) {
                    return '[' . $matches[1] . '](' . $matches[1] . ')';
                } else {
                    throw new \RuntimeException(sprintf("Text identified by '%d' has malformed BBCode urls", $this->id));
                }
            },
            $this->text
        );
    }

    /**
     * @brief Replaces BBCode images.
     */
    protected function replaceImages()
    {
        $this->text = preg_replace_callback(
            '%\[img\s*\]\s*("(?:[^"]*")|\A[^\']*\Z|(?:[^\'">\]\s]+))\s*(?:[^]\s]*)\[/img\]%iu',
            function ($matches) {
                if (isset($matches[1])) {
                    if ('cs' == $this->lang) {
                        return PHP_EOL . '![Obrázek vložený uživatelem](' . $matches[1] . ')' . PHP_EOL;
                    } else {
                        return PHP_EOL . '![Image uploaded by user](' . $matches[1] . ')' . PHP_EOL;
                    }
                } else {
                    throw new \RuntimeException(sprintf("Text identified by '%d' have malformed BBCode images", $this->id));
                }
            },
            $this->text
        );
    }

    /**
     * @brief Replaces BB2 images.
     */
    protected function replaceBB2Images()
    {
        $this->text = preg_replace_callback(
            '%\[imgLocal=("[^"]*"|[^\'">\]\s]+)\]([^[]*?)\[\/imgLocal\]%iu',
            function ($matches) {
                if (isset($matches[1])) {
                    if ('cs' == $this->lang) {
                        return PHP_EOL . '![Obrázek vložený uživatelem](' . $matches[1] . ')' . PHP_EOL;
                    } else {
                        return PHP_EOL . '![Image uploaded by user](' . $matches[1] . ')' . PHP_EOL;
                    }
                } else {
                    throw new \RuntimeException(sprintf("Text identified by '%d' have malformed BB2 images", $this->id));
                }
            },
            $this->text
        );
    }
    /**
     * @brief Replaces BBCode quotes and extracts the name.
     *
     * @details Thanks to Casimir et Hippolyte for helping me with this regex.
     */
    protected function replaceQuotes()
    {
        // Removes the inner quotes, leaving just one level.
        $this->text = preg_replace('~\G(?<!^)(?>(\[quote\b[^]]*](?>[^[]++|\[(?!/?quote)|(?1))*\[/quote])|(?<!\[)(?>[^[]++|\[(?!/?quote))+\K)|\[quote\b[^]]*]\K~', '', $this->text);

        // Replaces all the remaining quotes with '> ' characters and includes the name if present.
        $this->text = preg_replace_callback(
            '%\[quote(?:=([^\]/]+))?/?\]((?>[^[]++|\[(?!/?quote))*)\[/quote\]%i',
            function ($matches) {
                $name = isset($matches[1]) ? $matches[1] : 'Quote'; // Default to 'Quote' if no name is given.
                $quote = preg_replace('/^\s*/mu', '', trim($matches[2]));

                if ('cs' == $this->lang) {
                    return "> {$name} **napsal/a:**\n> " . str_replace("\n", "\n> ", $quote) . PHP_EOL . PHP_EOL;
                } else {
                    return "> {$name} **wrote:**\n> " . str_replace("\n", "\n> ", $quote) . PHP_EOL . PHP_EOL;
                }
            },
            $this->text
        );
    }

    /**
     * @brief Replaces BBCode snippets.
     */
    protected function replaceSnippets()
    {
        $this->text = preg_replace_callback(
            '%\[code\s*=?(?P<language>\w*)\](?P<snippet>[\W\D\w\s]*?)\[\/code\]%iu',
            function ($matches) {
                if (isset($matches['snippet'])) {
                    $language = strtolower($matches['language']);

                    if ('html4strict' == $language or 'div' == $language) {
                        $language = 'html';
                    } elseif ('shell' == $language or 'dos' == $language or 'batch' == $language) {
                        $language = 'sh';
                    } elseif ('xul' == $language or 'wpf' == $language) {
                        $language = 'xml';
                    } elseif ('asm' == $language) {
                        $language = 'nasm';
                    } elseif ('vb' == $language or 'visualbasic' == $language or 'vba' == $language) {
                        $language = 'vb.net';
                    } elseif ('asp' == $language) {
                        $language = 'aspx-vb';
                    } elseif ('xaml' == $language) {
                        $language = 'xml';
                    } elseif ('cplusplus' == $language) {
                        $language = 'cpp';
                    } elseif ('txt' == $language or 'gettext' == $language) {
                        $language = 'text';
                    } elseif ('basic' == $language) {
                        $language = 'cbmbas';
                    } elseif ('lisp' == $language) {
                        $language = 'clojure';
                    } elseif ('aspnet' == $language) {
                        $language = 'aspx-vb';
                    }

                    return PHP_EOL . '```' . $language . PHP_EOL . trim($matches['snippet']) . PHP_EOL . '```' . PHP_EOL;
                } else {
                    throw new \RuntimeException(sprintf("Text identified by '%d' has malformed BBCode snippet.", $this->id));
                }
            },
            $this->text
        );
    }

    public function clean()
    {
        // Define a list of known BBCode tags and their variants
        $bbcodeTags = [
            'quote' => ['\[quote\]', '\[\/quote\]'],
            'list' => ['\[list\]', '\[\/list\]'],
            'img' => ['\[img\]', '\[\/img\]'],
            'code' => ['\[code\]', '\[\/code\]'],
            'url' => ['\[url\]', '\[\/url\]'],
            'b' => ['\[b\]', '\[\/b\]'],
            'i' => ['\[i\]', '\[\/i\]'],
            'u' => ['\[u\]', '\[\/u\]'],
            's' => ['\[s\]', '\[\/s\]'],
            'center' => ['\[center\]', '\[\/center\]'],
            'left' => ['\[left\]', '\[\/left\]'],
            'color' => ['\[color\]', '\[\/color\]'],
            'size' => ['\[size\]', '\[\/size\]'],
        ];

        // Prepare a regular expression to match all the BBCode tags
        $pattern = '~(' . implode('|', array_merge(
            array_map(function ($tag) {
                return '(' . implode('|', $tag) . ')';
            }, $bbcodeTags),
            [
                // Patterns for common misspellings and variations
                '\[qoute\]', '\[quote\s*\]',
                '\[lsit\]', '\[list\s*\]',
                '\[imge\]', '\[img\s*\]',
                '\[cde\]', '\[code\s*\]',
                '\[ulr\]', '\[url\s*\]',
                '\[b\s*\]', '\[b\s*\]',
                '\[i\s*\]', '\[i\s*\]',
                '\[u\s*\]', '\[u\s*\]',
                '\[s\s*\]', '\[s\s*\]',
                '\[ceneter\]', '\[center\s*\]',
                '\[lef\]', '\[left\s*\]',
                '\[colr\]', '\[color\s*\]',
                '\[sze\]', '\[size\s*\]',
                // Patterns for missing brackets
                '\[quote\s*', '\[list\s*', '\[img\s*',
                '\[code\s*', '\[url\s*', '\[b\s*',
                '\[i\s*', '\[u\s*', '\[s\s*',
                '\[center\s*', '\[left\s*', '\[color\s*',
                '\[size\s*',
                'quote\]', 'list\]', 'img\]',
                'code\]', 'url\]', 'b\]',
                'i\]', 'u\]', 's\]',
                'center\]', 'left\]', 'color\]',
                'size\]'
            ]
        )) . ')~i';

        // Remove all identified tags
        $this->text = preg_replace($pattern, '', $this->text);
    }

    /**
     * @brief Converts the provided BBCode text to an equivalent Markdown text.
     */
    public function toMarkdown($text)
    {
        $this->text = $text;
        $this->removeCenter();
        $this->removeLeft();
        $this->removeColor();
        $this->removeSize();
        $this->replaceBold();
        $this->replaceItalic();
        $this->replaceUnderline();
        $this->replaceStrikethrough();
        $this->replaceLists();
        $this->replaceUrls();
        $this->replaceUndescribedUrls();
        $this->replaceImages();
        $this->replaceBB2Images();
        $this->replaceQuotes();
        $this->replaceSnippets();
        $this->clean();

        return trim($this->text);
    }
}
