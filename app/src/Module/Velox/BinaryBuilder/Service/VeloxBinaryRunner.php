<?php

declare(strict_types=1);

namespace App\Module\Velox\BinaryBuilder\Service;

use App\Module\Velox\BinaryBuilder\Exception\BuildException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final readonly class VeloxBinaryRunner
{
    public function __construct(
        private string $veloxBinaryPath = 'vx',
        private int $timeoutSeconds = 300,
    ) {}

    /**
     * Execute vx build command
     *
     * @return array{success: bool, output: string, errorOutput: string, exitCode: int}
     */
    public function build(string $configPath, string $outputPath): array
    {
        $command = [
            $this->veloxBinaryPath,
            'build',
            '-c',
            $configPath,
            '-o',
            $outputPath,
        ];

        $process = new Process($command);
        $process->setTimeout($this->timeoutSeconds);

        try {
            $process->run();

            return [
                'success' => $process->isSuccessful(),
                'output' => $process->getOutput(),
                'errorOutput' => $process->getErrorOutput(),
                'exitCode' => $process->getExitCode(),
            ];
        } catch (ProcessFailedException $e) {
            throw new BuildException(
                "Velox build process failed: {$e->getMessage()}",
                \explode("\n", $process->getOutput() . "\n" . $process->getErrorOutput()),
                $e,
            );
        }
    }

    /**
     * Check if vx binary is available
     */
    public function isVeloxAvailable(): bool
    {
        $process = new Process([$this->veloxBinaryPath, '--version']);
        $process->setTimeout(10);

        try {
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get vx binary version
     */
    public function getVeloxVersion(): ?string
    {
        if (!$this->isVeloxAvailable()) {
            return null;
        }

        $process = new Process([$this->veloxBinaryPath, '--version']);
        $process->setTimeout(10);

        try {
            $process->run();
            return \trim($process->getOutput());
        } catch (\Exception) {
            return null;
        }
    }
}
