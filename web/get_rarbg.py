import rarbgapi
from argparse import ArgumentParser
import json


def get_rarbg(query = None):
	if not query:
		parser = ArgumentParser()
		parser.add_argument('-query', help = "Rarbg searh query")
		args = parser.parse_args()
		query = args.query
	if not query:
		return 'Query is required, -h for help'
	client = rarbgapi.RarbgAPI()
	torrents = []
	for torrent in client.search(search_string = query, sort = 'last'):
	    torrents.append([torrent.filename, torrent.download])
	return json.dumps(torrents)

def main():
	print(get_rarbg())

if __name__ == '__main__':
    main()