<?php declare(strict_types=1);

namespace Sourcerer\Error;


/**
 * Reads the source lines around the failing line, the way the developer sees
 * them in an editor: the hit line is flagged and the window is bounded so a
 * large file never floods the response.
 */
final readonly class ErrorSnippet
{
    public function __construct(
        private int $pad = 10,
    ) { }


    /**
     * @return array<int, array{num: int, code: string, hit: bool}>
     */
    public function lines(string $file, int $line): array
    {
        if ($line < 1 || ! is_readable($file)) {
            return [];
        }

        $pad = max(5, min(30, $this->pad));
        $start = max($line - $pad, 1);
        $end = $line + $pad;

        $result = [];
        $handle = new \SplFileObject($file);

        for ($number = $start; $number <= $end; $number++) {
            try {
                $handle->seek($number - 1);
            }
            catch (\RuntimeException) {
                break;
            }

            if ( ! $handle->valid()) {
                break;
            }

            $current = $handle->current();

            $result[] = [
                'num' => $number,
                'code' => rtrim(is_string($current) ? $current : ''),
                'hit' => $number === $line,
            ];
        }

        return $result;
    }
}
