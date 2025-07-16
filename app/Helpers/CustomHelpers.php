<?php

if (!function_exists('generate_ticket_number')) {
    /**
     * Generates an array of unique ticket codes (max 10 characters, alphanumeric).
     *
     * @param int $amount Number of unique tickets to generate
     * @return array Array of unique ticket strings
     */
    function generate_ticket_number(int $amount): array
    {
        $tickets = [];

        while (count($tickets) < $amount) {
            // Microtime gives high-resolution time, helps avoid collisions
            $micro = microtime(true);
            $timestampInt = (int) str_replace('.', '', (string) $micro);

            // Base-36 encode the timestamp for compact representation
            $baseTime = base_convert($timestampInt, 10, 36); // e.g., "kfh7c9q4p"

            // Add 2-char randomness
            $random = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 2);

            // Combine and limit to 10 chars max
            $ticket = strtoupper(substr($baseTime . $random, 0, 10));

            // Ensure uniqueness within current batch
            if (!in_array($ticket, $tickets)) {
                $tickets[] = $ticket;
            }

            // Wait a tiny bit if generating many tickets very fast to avoid collision
            usleep(10); // 10 microseconds
        }

        return $tickets;
    }
}