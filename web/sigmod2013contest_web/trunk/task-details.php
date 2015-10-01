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
			<div class="logos">
	       		<a href="http://www.sigmod.org/" target="_blank"><img src="images/logo-acm-sigmod.jpg" alt="ACM SIGMOD" width="225"/></a>       
				<a href="http://www.microsoft.com/" target="_blank"><img src="images/logo-microsoft.jpg" alt="Microsoft" /></a>
				<a href="http://www.csail.mit.edu/" target="_blank"><img src="images/logo-mit.jpg" alt="MIT" /></a>
				<a href="http://www.kaust.edu.sa/" target="_blank"><img src="images/logo-kaust.jpg" alt="KAUST" /></a>
			</div>
			<br>

<!--=========================================================================-->
    <h1>
        Detailed Description
    </h1>
    <p>     
        A contestant must implement 4 major functions: StartQuery(), EndQuery(),
        MatchDocument(), and GetNextAvailRes(). The detailed parameters and 
        specifications of these functions are described <a href="doxygen/core_8h.html" target="_blank">here</a>.
    </p>
    <p>    
        These functions will be called by the testing framework. StartQuery()
        adds a query to the set of active queries, while EndQuery() removes it
        from that set.
        Each query is associated with the required matching type 
        (exact, edit distance, or Hamming distance), and matching distance 
        (for non exact matching). 
    </p>
    <p>        
        MatchDocument() matches a document with the current set of active queries,
        and saves the result somewhere in the main memory,
        until it is requested by GetNextAvailRes(). That is, the number of 
        calls to GetNextAvailRes() will be equal to the number of calls to 
        MatchDocument(). Instead of letting MatchDocument() return the results
        directly, GetNextAvailRes() is introduced to allow the contestant
        to process several calls to MatchDocument() at the same time, 
        using pthreads (which is recommended since the testing machine 
        has 12 cores). However, using parallelism is not a requirement.
    </p>
    <p>         
        To successfully deliver results of a document,
        GetNextAvailRes() must return the document ID, along with all query 
        IDs matching the document, sorted by query ID. Results of each document
        must be delivered exactly once. A call to GetNextAvailRes() must deliver
        results of any undelivered document, unless there are 
        no such results.
    </p>
    <p>         
        The total size of active queries and temporary results will be at
        most 25% of the available main memory. The total number of calls
        to StartQuery() will be at least twice as the total number of 
        calls to MatchDocument().
        Since we are trying to model real queries, many queries will exhibit
        significant overlap.      
    </p>
    <p>
        The task API header file, a basic implementation of the task interface,
        the test driver along with an example workload, and a
        Makefile are available in the tarball:
        <a href='files/sigmod2013contest-1.1.tar.gz'><strong>sigmod2013contest-1.1</strong></a>.
        The README file inside the tarball explains how to compile and
        run the code.
    </p>
<!--=========================================================================-->
    <h1>
        Evaluation Machine
    </h1>
    
    <p>
        The submissions will be evaluated on a machine with the following specs:
        
        <table style="margin:0 auto;">
            <tr><td><b>Processor</b></td><td>Intel Xeon X5650</td></tr>
            <tr><td><b>Processor Speed</b></td><td>2.67 GHz</td></tr>
            <tr><td><b>Configuration</b></td><td>2 processors (12 cores total)</td></tr>
            <tr><td><b>L1 Cache Size</b></td><td>64 KB/core</td></tr>
            <tr><td><b>L2 Cache Size</b></td><td>256 KB/core</td></tr>
            <tr><td><b>L3 Cache Size</b></td><td>12MB</td></tr>
            <tr><td><b>Main Memory</b></td><td>96 GB</td></tr>
            <tr><td><b>Hyper-Threading</b></td><td>Enabled</td></tr>
            <tr><td><b>Operating System</b></td><td>Ubuntu 12.10 (kernel: 3.5.0-17-generic)</td></tr>
            <tr><td><b>Compiler</b></td><td>GCC 4.7.2</td></tr>
        </table>
    </p>
<!--=========================================================================-->
    <h1>
        Contest Rules
    </h1>

    <p>
    * Teams must consist of individuals currently registered as students (graduate or
    undergraduate) in an academic institution. Teams can register on the contest site 
    after March 1, 2013. Multiple teams from an academic institution may participate.
    </p>
    <p>
    * Registered teams are allowed to submit (as a test) a 64-bits Linux library
    implementing the contest interface functions in C/C++. The test submission
    will be evaluated and its results will appear on the leaderboard. The team institution
    and country will not be shown in the leaderboard if this is specified during 
    team registration. Teams are allowed to make several test submissions.
    </p>
    <p>
    * Qualifying teams must submit the source code of their final implementation before 
    16 April. This final submission will be evaluated after the deadline and the 
    finalists will be chosen based on it. Teams can submit several times, 
    but only the last final submission will be considered.
    </p>
    <p>
    * A submission must contain only code written by the team or open-source
    licensed software. Source code from books or public articles is permitted,
    such that a clear note about the source exists.
    </p>
    <p>
    * By participating in this contest, each team agrees to publish its 
    source code in case it is selected as a finalist. A team is eligible 
    for the prize if at least one of its members will come to present his 
    work at the SIGMOD 2013 conference.
    </p>
<!--=========================================================================-->

</section>

<?php include('footer.php'); ?>
