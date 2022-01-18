# TreeDifference

TreeDifference is an library which will help you to get difference between two single or muldimensional array or object.
**Some useful information:**

- To use this library you have to include it in your file.
- After successfully include, you have to create object
- get_diff() - get difference
- set_remove() - will help you to remove some keys from array/object

### Prerequisites
<ul>
  <li><p>PHP >= 5.3</p></li>
</ul>

### Installation
<hr/>

```php
<?php
include 'pathto/TreeWalker.php';
```

### Example
```php
$array1 = array(1,2,[2,3,4],6);
$array2 = array(1,2,33,4);
$differ = new TreeDifference();
$differ->set_remove([1,2]);
$result = $differ->get_diff($array1 , $array2, true);
```
