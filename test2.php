<?php
set_time_limit(0);


print_r([12],[33]);exit;

for($i = 1;$i <= 9;$i ++){
	echo "  |  ";
	for($j=1;$j<=$i;$j++){

		echo "$j x $i =".$j*$i;
		echo "  |  ";
	}
	echo "<br/>";
}
exit;








