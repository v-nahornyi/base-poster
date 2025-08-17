<?php

namespace BasePoster;

defined( "ROOT_PATH" ) || exit;

use Dotenv\Dotenv;

class Poster {

	private string $botToken = '';
	private string $chatId = '';

	/**
	 * @throws \Exception
	 */
	public function __construct() {
		if ( class_exists('Dotenv\Dotenv' ) ) {
			$dotenv = Dotenv::createImmutable( ROOT_PATH );
			$dotenv->load();

			// Your bot's API token
			$this->botToken = $_ENV["BOT_TOKEN"];

			// The chat ID of your channel (e.g., @my_public_channel or -1001234567890)
			$this->chatId = $_ENV["CHAT_ID"];
		} else {
			throw new \Exception("Package vlucas/phpdotenv is missing. Cannot read values of chat ID and bot token from .env." );
		}
	}

	/**
	 * @throws \Exception
	 */
	public function sendMessage() {
		// The message you want to send
		$message = self::sanitizeJsonInput();
		$message = $message["link"] ?? null;

		if ( ! $message ) {
			throw new \Exception( "Message can't be empty" );
		}

		// Prepare the data to be sent
		$data = [
			'chat_id' => $this->chatId,
			'text'    => $message,
		];

		try {
			// The Telegram API endpoint for sending messages
			$apiUrl = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

			// Use http_build_query to format the data for the GET request
			$urlWithParams = $apiUrl . '?' . http_build_query( $data );

			// Send the request
			$response = file_get_contents( $urlWithParams );

			// Check the response
			if ( $response === false ) {
				return json_encode( [
					"error" => "Error: Could not send the message.",
				] );
			} else {
				file_put_contents( __DIR__ . "/json.txt", $response );
				$responseData = json_decode( $response, true );
				if ( $responseData['ok'] ) {
					return json_encode( [
						"url_sent" => true,
					] );
				} else {
					return json_encode( [
						"error" => 'Failed to send message:' . $responseData['description']
					] );
				}
			}
		} catch ( \Exception $e ) {
			return json_encode( [
				"error" => $e->getMessage()
			]);
		}
	}

	private static function sanitizeJsonInput() {
		// 1. Limit input size to prevent memory exhaustion
		$maxSize = 1024 * 1024; // 1MB limit
		$input   = file_get_contents( 'php://input', false, null, 0, $maxSize );

		if ( $input === false ) {
			throw new \Exception( 'Failed to read input' );
		}

		// 2. Validate JSON structure
		$data = json_decode( $input, true, 10 ); // Max depth of 10

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new \Exception( 'Invalid JSON: ' . json_last_error_msg() );
		}

		return $data;
	}
}