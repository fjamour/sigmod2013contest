<?php
/*
 * Copyright (C) 2013 by
 * Fuad Jamour <fjamour@gmail.com>
 * All rights reserved.
 * MIT license.
*/
?>

<?php include('header.php'); ?>

<section id="main">
	<h1>Submission Guidelines</h1>
	<p>
		The submission system accepts two formats: a 64-bit GNU/Linux shared
		library (libcore.so) or a tarball of your source code (impl.tar.gz).		
	</p>
	
	<h2>Submitting a shared library</h2>
	<p>
		To produce a valid shared library you can use the Makefile available in
		the contest tarball <a href='files/sigmod2013contest-1.1.tar.gz'><strong>sigmod2013contest-1.1</strong></a>.
		<br>
		Please follow the steps below:
		<br>
		1. In the file sigmod2013contest-1.1/Makefile, update the variable IMPL_O
		   with the target objects of your implementation
		<br>
		2. Navigate to sigmod2013contest-1.1 in a terminal and type 'make lib',
		   this will produce a shared library named 'libcore.so' with your implementation
		   of the task interface
		<br>
		3. Upload the file 'libcore.so' in the <a href="upload.php">upload page</a>

		<br>
		<br>
		Example valid shared library submission can be produced by navigating to sigmod2013contest-1.1
		and typing 'make lib'. This will produce a shared library from the reference implementation.
		<br>
		<br>
		Note: the submission system will not accept the submission if the name of the
		shared library is not 'libcore.so'.
	</p>
	<!--================================================================================-->
	<h2>Submitting source code</h2>
	<p>
		To submit source code and get it compiled on the evaluation machine, just produce a
		compressed tarball of the folder that has your implementation. Your implementation
		can span many files/folders. The name of the compressed tarball should be 'impl.tar.gz'.
		<br>
		You can produce the tarball by typing 'tar -czf impl.tar.gz &lt;YOUR_IMPLEMENTATION_FOLDER&gt;'
		on a GNU/Linux machine.
		<br>
		Example valid source code submissions are available at:
		<a href="files/valid_simple_source/impl.tar.gz">example 1</a>,
		<a href="files/valid_source/impl.tar.gz">example 2</a>,
		<a href="files/valid_source_tree/impl.tar.gz">example 3</a>.
		<br>
		<br>
		Note: this option can be used if you do not manage to compile a valid shared library
		on your side if, for example, you do not have access to a 64-bit GNU/Linux machine.
		Also, this option must be used to make the final submission (see Final Submission below).
	</p>
	<!--================================================================================-->
	<h2>Final submission</h2>
	<p>
		The last submitted source code (impl.tar.gz) that passes the tests will be considered your final
		submission. Please make sure that your final submission tarball has a README file with a small description
		of your implementation and a list of the team members names, institutions, and emails.
		
		<br>
		<br>
		Note: the final submission entry in the dashboard will have a dark green background.
	</p>
	<!--================================================================================-->
	<h2>Evaluation process</h2>
	<p>
		A submission goes through three steps before it is considered a valid submission:
		Small data test, Big data test, and New data test. In Small data test, the test driver 
		is run with a small data file (the example data file available in sigmod2013contest-1.1/test_data/small_test.txt)
		and is given a time limit of 60 seconds to finish. If the submission returns correct results for
		all the queries and it does not exceed the time limit it will appear on the leaderbaord. 
		If the submission finishes Small data test in less that 10 seconds, Big data test is started
		with a time limit of 5 minutes. If the submission finishes with correct results within 30 seconds, New data
		test is started with a time limit of 10 minutes.
		<br>
		<br>
		Please note that the current time limits are very generous, and that they will be decreased as the contest progresses.
		<br>
		<br>
		<strong>
		The final test data will have similar properties to New test data, but will be much larger in terms of the number
		of concurrent active queries (this number will not exceed one million).
		</strong>
	</p>
</section>
	    
<?php include('footer.php'); ?>
