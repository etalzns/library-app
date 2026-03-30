import sys
import qrcode

data = sys.argv[1]
filename = sys.argv[2]

img = qrcode.make(data)
img.save(filename)