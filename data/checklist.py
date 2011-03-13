# -*- coding: utf-8 -*-

fullset = set()
partset = set()
count = 0

for name in open("citylist"):
  name = name.replace("\n", "").decode("utf-8")
  partset.add(name[:-1])
  fullset.add(name)
  count += 1
  print name, name[:-1]

print count
print len(fullset)
print len(partset)
