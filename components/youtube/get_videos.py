from argparse import ArgumentParser
from yt_dlp import YoutubeDL
import json


def get_videos(url = None):
	if not url:
		parser = ArgumentParser()
		parser.add_argument('-url', help = "Youtube url")
		args = parser.parse_args()
		url = args.url
	if not url:
		return 'Url is required, -h for help'
	ydl = YoutubeDL({'dump_single_json': True, 'extract_flat': True, 'quiet': True}) 
	return json.dumps(ydl.extract_info(url, False))

def main():
	print(get_videos())

if __name__ == '__main__':
    main()