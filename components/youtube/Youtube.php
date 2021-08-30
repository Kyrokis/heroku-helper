<?

namespace app\components\youtube;

use app\components\Str;
use yii\helpers\ArrayHelper;

/**
 * Helper working with strings
 */
class Youtube {

	/**
	 * Get youtube playlist
	 * @param string $url
	 * @return string
	 */
	public static function getPlaylist($url, $reverse = false) {
		$return = "#EXTM3U\n";
		$command = 'python ' . __DIR__ . '/get_videos.py -url=' . $url;
		exec($command, $result);
		$result = json_decode($result[0]);
		$videos = $result->entries;
		if ($reverse) {
			$videos = array_reverse($videos);
		}
		foreach ($videos as $video) {
			$url = $video->url;
			$title = str_replace([',', '-'], [''], $video->title);
			$duration = $video->duration ? : 0;
			$uploader = $video->uploader ? $video->uploader . ' - ' : '';
			$return .= "#EXTINF:$duration,$uploader$title\nhttps://www.youtube.com/watch?v=$url\n";
		}
		$file = \Yii::$app->basePath . '/uploads/' . ($result->title ? : 'playlist') . '.m3u8';
		file_put_contents($file, $return);
		if (file_exists($file)) {
			return $file;
		}
		return false;
	}
}