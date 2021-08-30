from argparse import ArgumentParser
import youtube_dl
import json


def get_videos(url = None):
	if not url:
		parser = ArgumentParser()
		parser.add_argument('-url', help = "Youtube url")
		args = parser.parse_args()
		url = args.url
	if not url:
		return 'Url is required, -h for help'
	ydl = youtube_dl.YoutubeDL({'dump_single_json': True, 'extract_flat': True, 'quiet': True}) 
	return json.dumps(ydl.extract_info(url, False))

def main():
	print(get_videos())

if __name__ == '__main__':
    main()