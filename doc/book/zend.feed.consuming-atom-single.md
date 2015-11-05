# Consuming a Single Atom Entry

Single Atom `<entry>` elements are also valid by themselves. Usually the *URL* for an entry is the
feed's *URL* followed by `/<entryId>`, such as `http://atom.example.com/feed/1`, using the example
*URL* we used above. This pattern may exist for some web services which use *Atom* as a container
syntax.

If you read a single entry, you will have a `Zend\Feed\Reader\Entry\Atom` object.

## Reading a Single-Entry Atom Feed

``` sourceCode
$entry = Zend\Feed\Reader\Reader::import('http://atom.example.com/feed/1');
echo 'Entry title: ' . $entry->getTitle();
```
