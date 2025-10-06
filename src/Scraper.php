<?php
/**
 * Geekbench Browser Scraper
 *
 * Handles fetching and parsing Geekbench search results using Guzzle HTTP client
 * and Symfony DomCrawler for HTML parsing.
 *
 * @package GeekbenchScraper
 * @since 1.0.0
 * @version 1.0.0
 *
 * @example
 * $scraper = new Scraper();
 * $results = $scraper->fetch('iPhone18');
 */

namespace GeekbenchScraper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Scraper Class
 *
 * @since 1.0.0
 */
class Scraper {
    
    /**
     * Guzzle HTTP client instance
     *
     * @var Client
     */
    private $client;
    
    /**
     * Cache expiration time in seconds (15 minutes)
     *
     * @var int
     */
    private $cache_ttl = 900;
    
    /**
     * Base URL for Geekbench browser
     *
     * @var string
     */
    private $base_url = 'https://browser.geekbench.com';
    
    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Initialize Guzzle client with default options
        $this->client = new Client([
            'base_uri' => $this->base_url,
            'timeout' => 15.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
            ],
        ]);
        
        // Get cache TTL from options
        $this->cache_ttl = (int) get_option('geekbench_scraper_cache_ttl', 900);
    }
    
    /**
     * Fetch and parse Geekbench search results
     *
     * Retrieves search results from Geekbench Browser, parses the HTML,
     * and returns structured data. Results are cached for 15 minutes.
     *
     * @since 1.0.0
     *
     * @param string $query Search term (e.g., 'iPhone18', 'MacBook Pro')
     * @param bool $bypass_cache Whether to bypass cache and fetch fresh data
     * @return array Array of benchmark results with keys: system_name, upload_date,
     *               platform, single_core_score, multi_core_score, benchmark_url, processor_info
     * @throws \RuntimeException If HTTP request fails or HTML parsing fails
     *
     * @example
     * try {
     *     $results = $scraper->fetch('iPhone18');
     *     foreach ($results as $result) {
     *         echo $result['system_name'] . ': ' . $result['single_core_score'];
     *     }
     * } catch (\Exception $e) {
     *     error_log('Scraper error: ' . $e->getMessage());
     * }
     */
    public function fetch($query, $bypass_cache = false) {
        // Sanitize query
        $query = sanitize_text_field($query);
        
        if (empty($query)) {
            throw new \RuntimeException('Search query cannot be empty');
        }
        
        // Check cache first
        if (!$bypass_cache) {
            $cached = $this->get_cached_results($query);
            if (false !== $cached) {
                return $cached;
            }
        }
        
        try {
            // Fetch HTML from Geekbench
            $html = $this->fetch_html($query);

            // Parse HTML and extract data
            $results = $this->parse_html($html);

            // Post-process results to sanitize data
            $results = $this->sanitize_results($results);

            // Cache results
            $this->cache_results($query, $results);

            return $results;
            
        } catch (GuzzleException $e) {
            throw new \RuntimeException(
                sprintf('Failed to fetch Geekbench results: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
    
    /**
     * Fetch HTML from Geekbench search URL
     *
     * @since 1.0.0
     *
     * @param string $query Search query
     * @return string HTML content
     * @throws GuzzleException If HTTP request fails
     */
    private function fetch_html($query) {
        $response = $this->client->get('/search', [
            'query' => ['q' => $query],
        ]);
        
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(
                sprintf('Geekbench returned status code %d', $response->getStatusCode())
            );
        }
        
        return (string) $response->getBody();
    }
    
    /**
     * Parse HTML and extract benchmark results
     *
     * @since 1.0.0
     *
     * @param string $html HTML content to parse
     * @return array Array of parsed results
     */
    public function parse_html($html) {
        $crawler = new Crawler($html);
        $results = [];
        
        try {
            // Find all result containers
            $crawler->filter('div.col-12.list-col')->each(function (Crawler $node) use (&$results) {
                try {
                    // Extract data using the correct structure
                    $result = [
                        'system_name' => $this->extract_system_name($node),
                        'benchmark_url' => $this->extract_attr($node, '.col-12.col-lg-4 a', 'href'),
                        'processor_info' => $this->extract_processor_info($node),
                        'upload_date' => $this->extract_upload_date($node),
                        'platform' => $this->extract_platform($node),
                        'single_core_score' => $this->extract_score($node, 0),
                        'multi_core_score' => $this->extract_score($node, 1),
                    ];

                    // Add full benchmark URL
                    if (!empty($result['benchmark_url'])) {
                        $result['benchmark_url'] = $this->base_url . $result['benchmark_url'];
                    }

                    // Only add if we have minimum required data
                    if (!empty($result['system_name']) && !empty($result['single_core_score'])) {
                        $results[] = $result;
                    }
                } catch (\Exception $e) {
                    // Skip malformed entries
                    error_log('Geekbench Scraper: Failed to parse result - ' . $e->getMessage());
                }
            });
        } catch (\Exception $e) {
            error_log('Geekbench Scraper: Failed to parse HTML - ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Sanitize results data
     *
     * Post-processes the parsed results to clean up and normalize data.
     * Specifically handles cleaning upload_date field to remove usernames
     * and other extraneous text.
     *
     * @since 1.0.0
     *
     * @param array $results Array of parsed results
     * @return array Sanitized results
     */
    private function sanitize_results($results) {
        foreach ($results as &$result) {
            // Sanitize upload_date to remove usernames and extra text
            if (!empty($result['upload_date'])) {
                $result['upload_date'] = $this->sanitize_upload_date($result['upload_date']);
            }
        }
        return $results;
    }

    /**
     * Sanitize upload date string
     *
     * Removes usernames and other extraneous text from the upload date,
     * keeping only the date portion in format "MMM DD, YYYY".
     *
     * @since 1.0.0
     *
     * @param string $date_string Raw date string that may contain username
     * @return string Cleaned date string
     */
    private function sanitize_upload_date($date_string) {
        // Pattern to match date format: "Oct 06, 2025"
        // This matches: Month (3 letters) + space + day (1-2 digits) + comma + space + year (4 digits)
        if (preg_match('/([A-Z][a-z]{2}\s+\d{1,2},\s+\d{4})/', $date_string, $matches)) {
            return $matches[1];
        }

        // If no match, return the original (shouldn't happen with valid data)
        return $date_string;
    }


    /**
     * Extract attribute from node using CSS selector
     *
     * @since 1.0.0
     *
     * @param Crawler $node Parent node
     * @param string $selector CSS selector
     * @param string $attribute Attribute name
     * @return string Extracted attribute value
     */
    private function extract_attr(Crawler $node, $selector, $attribute) {
        try {
            $element = $node->filter($selector);
            if ($element->count() > 0) {
                return $element->attr($attribute);
            }
        } catch (\Exception $e) {
            // Return empty string on error
        }
        return '';
    }
    
    /**
     * Extract score from node
     *
     * @since 1.0.0
     *
     * @param Crawler $node Parent node
     * @param int $index Score index (0 = single-core, 1 = multi-core)
     * @return int Score value
     */
    private function extract_score(Crawler $node, $index) {
        try {
            $scores = $node->filter('.list-col-text-score');
            if ($scores->count() > $index) {
                return (int) trim($scores->eq($index)->text());
            }
        } catch (\Exception $e) {
            // Return 0 on error
        }
        return 0;
    }

    /**
     * Extract system name from node
     *
     * @since 1.0.0
     *
     * @param Crawler $node Parent node
     * @return string System name
     */
    private function extract_system_name(Crawler $node) {
        try {
            $link = $node->filter('.col-12.col-lg-4 a');
            if ($link->count() > 0) {
                return trim($link->text());
            }
        } catch (\Exception $e) {
            // Return empty string on error
        }
        return '';
    }

    /**
     * Extract processor info from node
     *
     * @since 1.0.0
     *
     * @param Crawler $node Parent node
     * @return string Processor info
     */
    private function extract_processor_info(Crawler $node) {
        try {
            $model = $node->filter('.list-col-model');
            if ($model->count() > 0) {
                return trim($model->text());
            }
        } catch (\Exception $e) {
            // Return empty string on error
        }
        return '';
    }

    /**
     * Extract upload date from node
     *
     * @since 1.0.0
     *
     * @param Crawler $node Parent node
     * @return string Upload date
     */
    private function extract_upload_date(Crawler $node) {
        try {
            // Find the column with "Uploaded" subtitle
            $columns = $node->filter('.col-6.col-md-3.col-lg-2');
            foreach ($columns as $column) {
                $col_crawler = new Crawler($column);
                $subtitle = $col_crawler->filter('.list-col-subtitle');
                if ($subtitle->count() > 0 && trim($subtitle->text()) === 'Uploaded') {
                    $text = $col_crawler->filter('.list-col-text');
                    if ($text->count() > 0) {
                        // Get the full text content
                        $full_text = $text->text();

                        // Split by newlines and take the first non-empty line
                        $lines = preg_split('/[\r\n]+/', $full_text);
                        foreach ($lines as $line) {
                            $line = trim($line);
                            // Return the first line that looks like a date
                            // (contains month abbreviation or is in format like "Oct 06, 2025")
                            if (!empty($line) && preg_match('/^[A-Z][a-z]{2}\s+\d{1,2},\s+\d{4}/', $line)) {
                                return $line;
                            }
                        }

                        // Fallback: just return the first non-empty line
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (!empty($line)) {
                                return $line;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Return empty string on error
        }
        return '';
    }

    /**
     * Extract platform from node
     *
     * @since 1.0.0
     *
     * @param Crawler $node Parent node
     * @return string Platform
     */
    private function extract_platform(Crawler $node) {
        try {
            // Find the column with "Platform" subtitle
            $columns = $node->filter('.col-6.col-md-3.col-lg-2');
            foreach ($columns as $column) {
                $col_crawler = new Crawler($column);
                $subtitle = $col_crawler->filter('.list-col-subtitle');
                if ($subtitle->count() > 0 && trim($subtitle->text()) === 'Platform') {
                    $text = $col_crawler->filter('.list-col-text');
                    if ($text->count() > 0) {
                        return trim($text->text());
                    }
                }
            }
        } catch (\Exception $e) {
            // Return empty string on error
        }
        return '';
    }
    
    /**
     * Get cache key for query
     *
     * @since 1.0.0
     *
     * @param string $query Search query
     * @return string Cache key
     */
    public function get_cache_key($query) {
        return 'geekbench_scraper_' . md5($query);
    }
    
    /**
     * Get cached results
     *
     * @since 1.0.0
     *
     * @param string $query Search query
     * @return array|false Cached results or false if not found
     */
    private function get_cached_results($query) {
        return get_transient($this->get_cache_key($query));
    }
    
    /**
     * Cache results
     *
     * @since 1.0.0
     *
     * @param string $query Search query
     * @param array $results Results to cache
     * @return bool True on success
     */
    private function cache_results($query, $results) {
        return set_transient(
            $this->get_cache_key($query),
            $results,
            $this->cache_ttl
        );
    }
    
    /**
     * Clear cache for specific query
     *
     * @since 1.0.0
     *
     * @param string $query Search query
     * @return bool True on success
     */
    public function clear_cache($query) {
        return delete_transient($this->get_cache_key($query));
    }
}

