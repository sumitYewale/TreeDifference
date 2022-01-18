# TreeDifference

Example
$array1 = array(1,2,[2,3,4],6);
$array2 = array(1,2,33,4);
$differ = new TreeDifference();
$differ->set_remove([1,2]);
$result = $differ->get_diff($array1 , $array2, true);
