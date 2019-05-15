
import os
import csv
import requests
import tqdm


FILE = './1.csv'
RESULT = './result-{}.txt'.format(os.path.basename(FILE))
SHIT = './shit.csv'

def clear_file(file):
    if os.path.isfile(file):
        os.remove(file)
    else:
        print("Info: %s file not found" % file)


clear_file(RESULT)
f = open(RESULT, 'a', encoding="utf-8")

clear_file(SHIT)
shit = open(SHIT, 'a', encoding="utf-8")

with open(FILE, newline='', encoding="utf-8-sig") as csvfile:
    spamreader = csv.reader(csvfile, delimiter=',')

    with tqdm.tqdm(total=sum(1 for line in open(FILE,'r'))) as pbar:
        for row in spamreader:
            if not row:
                continue

            try:
                r = requests.get(row[0])
                print('{} -- {}'.format(r.status_code, row[0]), file=f)
            except requests.ConnectionError:
                print('{} -- {}'.format('FAILED', row[0]), file=f)


            for h in r.history:
                print('redirect {} -> {}'.format(h.status_code, r.url), file=f)

            if r.url == row[2]:
                print('STRAIGHT\n', file=f)
            else:
                print('HMM /:\n', file=f)

            if r.status_code != 200:
                shit.write(','.join(row) + '\n')
            pbar.update(1)

f.close()
shit.close()
