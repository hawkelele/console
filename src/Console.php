<?php

/**
 * Message output class for console or web based scripts
 * It supports color based output for both execution environments
 *
 * @author Stefano <buico@archivium.digital>
 */

namespace Hawkelele\Console;

use Exception;

class Console
{
    protected $platform;
    protected $logpath;

    protected $theme = "dark";

    protected $colors = [
        "black" => "\e[0m",
        "red" => "\e[31m",
        "green" => "\e[32m",
        "orange" => "\e[33m",
        "blue" => "\e[34m",
        "gray" => "\e[90m",
        "grey" => "\e[90m",
    ];

    /**
     * @param string $logpath   Log file path
     * @param string $theme     dark|light Theme for web based execution
     */
    public function __construct(?string $logpath = null, string $theme = 'dark')
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $this->platform = 'console';
        } else {
            if (!in_array($this->theme, ["dark", "light"])) {
                throw new Exception("Invalid theme. Theme must be one of 'dark' and 'light'");
            }

            $this->theme = $theme;

            $themecolor = $this->theme === 'dark' ? "white" : "black";
            $themebackground = $this->theme === 'dark' ? "#333" : "#eee";

            print("<pre>");
            print("<style>");
            print("
                body {
                    background: $themebackground;
                    color: $themecolor;
                    font-size: 1.10rem;
                    max-width: 1200px;
                    margin: 1.15rem auto;
                }
        
                pre {
                    white-space: pre-wrap;
                    word-wrap: break-word;
                }
            ");
            print("</style>");
            $this->platform = 'web';
        }

        $this->logpath = $logpath;
    }

    /**
     * Outputs a message and, if set, writes it to the log file
     *
     * @param string $message   Message
     * @param string $color     Message color
     * @return void
     */
    protected function out(string $message, string $color = 'black')
    {
        if (isset($this->logpath)) {
            if (!file_exists(dirname($this->logpath))) {
                mkdir(dirname($this->logpath), 0755, true);
            }
            file_put_contents($this->logpath, "[" . date("c") . "] " . $message . "\n", FILE_APPEND);
        }

        if ($this->platform === 'console') {
            $message = $this->colors[$color] . $message . "\e[0m\n";
        } else {
            $message = "<span style=\"color: $color;\">$message</span>\n";
        }

        print($message);
    }

    /**
     * Outputs a message
     *
     * @param string $message
     * @param string $color
     * @return void
     */
    public function message(string $message, string $color = "black")
    {
        if ($this->platform === "web" && $color === "black" && $this->theme === "dark") {
            $color = "white";
        }

        $this->out($message, $color);
    }

    /**
     * Outputs a red colored message
     *
     * @param string $message
     * @return void
     */
    public function error(string $message)
    {
        $this->out($message, 'red');
    }

    /**
     * Outputs an orange colored message
     *
     * @param string $message
     * @return void
     */
    public function warning(string $message)
    {
        $this->out($message, 'orange');
    }

    /**
     * Outputs a green colored message
     *
     * @param string $message
     * @return void
     */
    public function success(string $message)
    {
        $this->out($message, 'green');
    }
}
