<?php



class Dotenv_Parser
{
    const INITIAL_STATE = 0;
    const QUOTED_STATE = 1;
    const ESCAPE_STATE = 2;
    const WHITESPACE_STATE = 3;
    const COMMENT_STATE = 4;

    /**
     * Parse the given variable name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function parseName($name)
    {
        return trim(str_replace(array('export ', '\'', '"'), '', $name));
    }

    /**
     * Parse the given variable value.
     *
     * @param string $value
     *
     * @throws Dotenv_Exception_InvalidFileException
     *
     * @return string
     */
    public static function parseValue($value)
    {
        if ($value === '') {
            return '';
        } elseif ($value[0] === '"' || $value[0] === '\'') {
            return static::parseQuotedValue($value);
        } else {
            return static::parseUnquotedValue($value);
        }
    }

    /**
     * Parse the given quoted value.
     *
     * @param string $value
     *
     * @throws Dotenv_Exception_InvalidFileException
     *
     * @return string
     */
    public static function parseQuotedValue($value)
    {
        $data = array_reduce(str_split($value), function ($data, $char) use ($value) {
            switch ($data[1]) {
                case static::INITIAL_STATE:
                    if ($char === '"' || $char === '\'') {
                        return array($data[0], static::QUOTED_STATE);
                    } else {
                        throw new Dotenv_Exception_InvalidFileException(
                            'Expected the value to start with a quote.'
                        );
                    }
                case static::QUOTED_STATE:
                    if ($char === $value[0]) {
                        return array($data[0], static::WHITESPACE_STATE);
                    } elseif ($char === '\\') {
                        return array($data[0], static::ESCAPE_STATE);
                    } else {
                        return array($data[0].$char, static::QUOTED_STATE);
                    }
                case static::ESCAPE_STATE:
                    if ($char === $value[0] || $char === '\\') {
                        return array($data[0].$char, static::QUOTED_STATE);
                    } else {
                        return array($data[0].'\\'.$char, static::QUOTED_STATE);
                    }
                case static::WHITESPACE_STATE:
                    if ($char === '#') {
                        return array($data[0], static::COMMENT_STATE);
                    } elseif (!ctype_space($char)) {
                        throw new Dotenv_Exception_InvalidFileException(
                            'Dotenv values containing spaces must be surrounded by quotes.'
                        );
                    } else {
                        return array($data[0], static::WHITESPACE_STATE);
                    }
                case static::COMMENT_STATE:
                    return array($data[0], static::COMMENT_STATE);
            }
        }, array('', static::INITIAL_STATE));

        return trim($data[0]);
    }

    /**
     * Parse the given unquoted value.
     *
     * @param string $value
     *
     * @throws Dotenv_Exception_InvalidFileException
     *
     * @return string
     */
    public static function parseUnquotedValue($value)
    {
        $parts = explode(' #', $value, 2);
        $value = trim($parts[0]);

        // Unquoted values cannot contain whitespace
        if (preg_match('/\s+/', $value) > 0) {
            // Check if value is a comment (usually triggered when empty value with comment)
            if (preg_match('/^#/', $value) > 0) {
                $value = '';
            } else {
                throw new Dotenv_Exception_InvalidFileException('Dotenv values containing spaces must be surrounded by quotes.');
            }
        }

        return trim($value);
    }
}
