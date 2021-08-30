<?php
namespace app\components\telegram;

/**
 * @author Akbar Joudi <akbar.joody@gmail.com>
 */
class TelegramBase extends \aki\telegram\Telegram
{

	 /**
     * @var Input
     */
    private $_input;

	 /**
	 * @return \Input
	 */
	/**
	 * @return Input
	 */
	protected function getInput(): ?Input
	{
		if (empty($this->_input)) {
			$input = file_get_contents('php://input');
			if (!$input) {
				$this->_input = null;
			} else {
				try {
					$array = json_decode($input, true, 512, JSON_THROW_ON_ERROR);
					$this->_input = new Input($array);
				}
				catch (\Exception $ex) {
					return null;
				}
			}
		}

		return $this->_input;
	}

	/**
	 * initializeParams
	 * @param Array $params
	 */
	public function initializeParams($params)
	{
		$is_resource = false;
		$multipart    = [];

		if (empty($params)) {
			return [];
		}

		//Reformat data array in multipart way if it contains a resource
		$attachments = ['photo', 'sticker', 'audio', 'document', 'video', 'voice', 'animation', 'video_note', 'thumb'];
		foreach ($params as $key => $item) {
			if ($key === 'media') {
				// Magical media input helper.
				$item = $this->mediaInputHelper($item, $is_resource, $multipart);
			} else if (in_array($key, $attachments)) {
				if (file_exists($item)) {
					$file = fopen($item, 'r');
					$is_resource |= is_resource($file);
					$multipart[] = ['name' => $key, 'contents' => $file];
					break;
				}
			}


			$multipart[]  = ['name' => $key, 'contents' => $item];
		}
		if ($is_resource) {

			return ['multipart' => $multipart];
		}

		return ['form_params' => $params];
	}

	public function editMessageReplyMarkup(array $params = [])
	{
		$body = $this->send("/editMessageReplyMarkup", $params);
		//$response = new Response($body);
        return $body;
	}

	public function deleteMessage(array $params = [])
    {
        $body = $this->send("/deleteMessage", $params);
        //$response = new Response($body);
        return $body;
    }
}
