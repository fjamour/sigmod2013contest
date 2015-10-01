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
			<!--
			<div class="logos">
                <a href="http://www.sigmod.org/" target="_blank"><img style="margin-right: 100px;" src="images/logo-acm-sigmod.jpg" width="27%" alt="ACM SIGMOD" /></a>
                <a href="http://www.csail.mit.edu/" target="_blank"><img style="margin-right: 100px;" src="images/logo-mit-2.png" width="17%" alt="MIT" /></a>
                <a href="http://www.kaust.edu.sa/" target="_blank"><img src="images/logo-kaust-2.png" width="18%" alt="KAUST" /></a>
            </div>
            -->
<!--=========================================================================-->            
            <h1>News and Updates</h1>
            <table cellpadding="0" cellspacing="0" class="no_background">
                <tr>
                    <td width="200">May 2, 2013</td>
                    <td>
                           <a href="http://www.microsoft.com/" target="_blank">Microsoft</a> donated USD $5,000 prize for the winning team
                    </td>
                </tr>
                <tr>
                    <td width="200">May 1, 2013</td>
                    <td>
                           <a href="finalists.php">Five finalists</a> have been selected
                    </td>
                </tr>
                <tr>
                    <td width="200">Apr 2, 2013</td>
                    <td>
                           <a href="https://docs.google.com/file/d/0B9lJ0WMNHYqCTW9Bc2p2TXVWSTA/edit?usp=sharing" target="_blank">Intermediate test</a>, a test similar to New test but smaller, is now publicly available
                    </td>
                </tr>
                <tr>
                    <td width="200">Mar 18, 2013</td>
                    <td>
                           <a href="https://docs.google.com/file/d/0B9lJ0WMNHYqCaUs2ODVjNjFvRms/edit?usp=sharing" target="_blank">Big test</a> is now publicly available
                    </td>
                </tr>
                <tr>
                    <td width="200">Mar 1, 2013</td>
                    <td>
                           <a href="create_account.php">Registration</a> open and <a href="leaderboard.php">leaderboard</a> available
                    </td>
                </tr>
                <tr>
                    <td width="200">Feb 1, 2013</td>
                    <td>
                           Released detailed <a href="task-details.php">task description</a>
                    </td>
                </tr>                
                <tr>
                    <td width="200">Jan 23, 2013</td>
                    <td>Contest announced</td>
                </tr>
            </table>
<!--=========================================================================-->            
            <h2>Overview</h2>
            <p>
                Student teams from degree granting institutions are invited
                to compete in the annual SIGMOD programming contest.
                This year, the task is to implement a streaming document
                filtering system. The winning team will be awarded a prize
                of USD $5,000 donated by <a href="http://www.microsoft.com/" target="_blank">Microsoft</a>.
                Second and third teams will be awarded USD $3,000 and $2,000 respectively, donated by <a href="http://www.kaust.edu.sa/" target="_blank">KAUST</a>.
                Submissions will be judged based on their
                overall performance on a supplied workload. Teams with the 
                top-performing submissions will receive travel grants
                to attend 
                <a href="http://www.sigmod.org/2013/" title="SIGMOD 2013" target="_blank">SIGMOD 2013 in New York, USA</a>
                .
            </p>
<!--=========================================================================-->            
            <h2>Task Overview</h2>
            <p>
                The general idea is to filter a stream of documents using a
                dynamic set of exact and approximate continuous keyword match.
            </p>
            <p>
                Specifically, the goal is to maximize the throughput with
                which documents are disseminated to active queries.
                Whenever a new document arrives, the system must
                quickly determine all queries satisfied by this document.
                Each query is represented as a set of keywords, and each
                document is represented as a sequence of space separated
                words. For a document to satisfy a query it should contain
                all the words in the query. Queries will fit into the main
                memory of the machine. Three types of keyword matching must
                be supported: exact matches, approximate matches under an
                edit distance constraint, and approximate matches under 
                a Hamming distance constraint.
            </p>
            <p>    
                There are many practical examples where such a system
                can be used. For example, if one thinks of tweets as
                documents, and queries as some hash-tags a user wishes
                to follow, then it is desirable to know which users should
                be notified when a new tweet arrives. Another example is
                subscriptions to news services, where a user wishes to be
                notified each time an article of interest is published. The
                problem becomes challenging when the query set is big and
                changes frequently.
            </p>
            <p>
                Since users interested in the same area usually submit
                similar queries, one particular focus will be on the practical
                case where a large number of queries have similar words.
            </p>
<!--=========================================================================-->            
            <h2>Important Dates</h2>
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td width="150">Feb 1, 2013</td>
                    <td width="734">The detailed specification of the requirements will be available on the website.</td>
                </tr>
                <tr>
                    <td>Mar 1, 2013</td>
                    <td>Team registration starts, and leaderboard becomes available.</td>
                </tr>
                <tr>
                    <td>Apr 15, 2013</td>
                    <td>Submission deadline.</td>
                </tr>
                <tr>
                    <td>May 1, 2013</td>
                    <td>Finalists will be notified.</td>
                </tr>
            </table>
<!--=========================================================================-->            
            <h2>Contact Information</h2>
            <p>
                To stay updated or to ask technical questions please join
                <a href="https://groups.google.com/forum/?fromgroups#!forum/sigmod2013contest" target="_blank">sigmod2013contest</a>
                group.
            </p>
            <p>
                For non-technical questions, please contact:
                <br>
                Research Assistant: Amin Allam (amin.allam@kaust.edu.sa)
                <br>
                Research Assistant: Fuad Jamour (fuad.jamour@kaust.edu.sa)
                <br>
                Associate Professor: Dr. Panos Kalnis (panos.kalnis@kaust.edu.sa)
            </p>
<!--=========================================================================-->            
            <h2>Organizers</h2>
            <p>
                The contest is organized by the 
                <a href="http://cloud.kaust.edu.sa/" target="_blank" title="InfoCloud Research Group">InfoCloud Research Group</a>
                in
                <a href="http://www.kaust.edu.sa/" target="_blank" title="KAUST">King Abdullah University of Science and Technology (KAUST)</a>
                in collaboration with the
                <a href="http://www.csail.mit.edu/" target="_blank" title="MIT Computer Science and Artificial Intelligence Laboratory">MIT Computer Science and Artificial Intelligence Laboratory (CSAIL)</a>
                .
            </p>
<!--=========================================================================-->            
            <h2>Sponsorship</h2>
            <p>
                Prize money for the winning team is gracefully donated by <a href="http://www.microsoft.com/" target="_blank" title="Microsoft">Microsoft</a>.
                Prize money for the second and third teams, and the finalists' travel grants are covered by a grant from
                <a href="http://www.kaust.edu.sa/" target="_blank" title="KAUST">King Abdullah University of Science and Technology (KAUST)</a>.
            </p>
<!--=========================================================================-->

			<br><br>
            <p>
		        <div style="text-align:center;">
		            <a href="https://twitter.com/sigmod13contest/" target="_blank"><img src="images/t_icon.jpg" width="40px" alt="Twitter handle" /></a>
		            &nbsp; &nbsp; &nbsp; &nbsp;
		            <a href="https://www.facebook.com/sigmod2013contest/" target="_blank"><img src="images/fb_icon.png" width="40px" alt="fb handle" /></a>
		        </div>
            </p>
<!--=========================================================================-->
        </section>

<?php include('footer.php'); ?>
