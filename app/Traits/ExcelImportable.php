<?php

namespace App\Traits;

trait ExcelImportable
{
    /**
     * Get the value of a given column from the current row.
     *
     * It checks whether the column exists in the Excel import mapping,
     * and if found, retrieves its value from the row. If not found, returns null.
     *
     * @param string $column The column name to search for.
     *
     * @return mixed|null Returns the value from the row, or null if not found.
     */
    private function getColumnValue(string $column)
    {
        return $this->isColumnExists($column)
            ? $this->row[array_keys($this->columns, $column)[0]]
            : null;
    }

    /**
     * Check if a given column exists in the imported Excel mapping.
     *
     * @param string $column The column name to check.
     *
     * @return bool True if the column exists, false otherwise.
     */
    private function isColumnExists(string $column)
    {
        return !empty(array_keys($this->columns, $column));
    }

    /**
     * Convert an array of values into a comma-separated string.
     *
     * Useful for logging failed rows with context.
     *
     * @param array $values The values to join.
     *
     * @return string A string of values separated by commas.
     */
    private function getRowValuesAsString(array $values)
    {
        return implode(', ', $values);
    }

    /**
     * Mark the job as failed with a custom message and append row data.
     *
     * @param string $message The error message prefix.
     *
     * @return void
     */
    private function failJob(string $message)
    {
        $this->job->fail(
            $message . $this->getRowValuesAsString($this->row)
        );
    }

    /**
     * Mark the job as failed with only a message (no row data).
     *
     * @param string $message The error message.
     *
     * @return void
     */
    private function failJobWithMessage(string $message)
    {
        $this->job->fail($message);
    }

    /**
     * Validate whether an email address is in a correct format.
     *
     * @param string|null $email The email address to validate.
     *
     * @return bool True if valid, false otherwise.
     */
    private function isEmailValid(string|null $email)
    {
        if (empty($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
