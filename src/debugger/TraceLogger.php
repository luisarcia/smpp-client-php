<?php

declare(strict_types=1);

namespace Larc\SMPPClient\debugger;

/**
 * TraceLogger
 * Class to log trace messages.
 */
class TraceLogger
{
    private $traceEnabled;
    private $messages = [];

    public function __construct(bool $traceEnabled = false)
    {
        $this->traceEnabled = $traceEnabled;
    }

    /**
     * Method write
     * Write a message to the trace log
     *
     * @param string $message Message to write
     *
     * @return void
     */
    public function write(string $message): void
    {
        if ($this->traceEnabled) {
            $this->messages[] = date('Y-m-d H:i:s') . " - " . $message;
        }
    }

    /**
     * Method display
     * Display the trace log
     *
     * @return void
     */
    public function display(): void
    {
        if ($this->traceEnabled && !empty($this->messages)) {
            echo '<pre style="background: #FAFAFA; color: #001950; border-radius: 10px; padding: 10px">';
            foreach ($this->messages as $message) {
                echo $message . "\n";
            }
            echo "</pre>";
        }
    }

    /**
     * Method __destruct
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        $this->display();
    }

    /**
     * Method enableTrace
     * Enable trace
     *
     * @return void
     */
    public function enableTrace(): void
    {
        $this->traceEnabled = true;
    }

    /**
     * Method disableTrace
     * Disable trace
     *
     * @return void
     */
    public function disableTrace(): void
    {
        $this->traceEnabled = false;
    }

    /**
     * Method getState
     * Get trace state
     *
     * @return bool
     */
    public function getState() : bool
    {
        return $this->traceEnabled;
    }
}
