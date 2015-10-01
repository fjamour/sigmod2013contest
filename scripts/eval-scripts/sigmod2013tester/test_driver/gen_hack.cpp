/*
 * gen_sol.cpp version 1.0
 * Copyright (c) 2013 KAUST - InfoCloud Group (All Rights Reserved)
 * Author: Amin Allam
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

#define GENERATE_HACK
#ifdef GENERATE_HACK

#include "core.h"
#include <cstdlib>
#include <cstdio>
#include <string>
using namespace std;

///////////////////////////////////////////////////////////////////////////////////////////////

#include <sys/time.h>
int GetClockTimeInMilliSec()
{
	struct timeval t2; gettimeofday(&t2,NULL);
	return t2.tv_sec*1000+t2.tv_usec/1000;
}
void PrintTime(int milli_sec, FILE* out_file)
{
	int v=milli_sec;
	int hours=v/(1000*60*60); v%=(1000*60*60);
	int minutes=v/(1000*60); v%=(1000*60);
	int seconds=v/1000; v%=1000;
	int milli_seconds=v;
	int first=1;
	fprintf(out_file, "%d milli-seconds [", milli_sec);
	if(hours) {if(!first) fprintf(out_file, ":"); fprintf(out_file, "%dh", hours); first=0;}
	if(minutes) {if(!first) fprintf(out_file, ":"); fprintf(out_file, "%dm", minutes); first=0;}
	if(seconds) {if(!first) fprintf(out_file, ":"); fprintf(out_file, "%ds", seconds); first=0;}
	if(milli_seconds) {if(!first) fprintf(out_file, ":"); fprintf(out_file, "%dms", milli_seconds); first=0;}
	fprintf(out_file, "]");
}

///////////////////////////////////////////////////////////////////////////////////////////////

//char temp[MAX_DOC_LENGTH];
char temp_me[MAX_DOC_LENGTH*2];

unsigned int GetUnsignedInteger(char*& cur_temp_loc)
{
	unsigned int val=0;
	while(1)
	{
		char ch=*cur_temp_loc;
		if(ch<'0' || ch>'9') return val;
		val=val*10+ch-'0';
		cur_temp_loc++;
	}
	return val;
}

int GetInteger(char*& cur_temp_loc)
{
	int val=0;
	while(1)
	{
		char ch=*cur_temp_loc;
		if(ch<'0' || ch>'9') return val;
		val=val*10+ch-'0';
		cur_temp_loc++;
	}
	return val;
}

void TestSigmod(const char* test_file_str, int time_limit_seconds, FILE* out_file, unsigned int last_doc_id)
{
	int i;
	fprintf(out_file, "Start Test ...\n"); fflush(out_file);
	FILE* test_file=fopen(test_file_str, "rt");

	if(!test_file)
	{
		fprintf(out_file, "Cannot Open File %s\n", test_file_str);
		fflush(out_file);
		return;
	}

	int v=GetClockTimeInMilliSec();

	unsigned int num_processed_docs=0;

	FILE* sol_file=fopen("res_sol.txt", "wt");

	while(1)
	{
		char ch=0;
		unsigned int id=0;

		// fixed bug of last batch
		temp_me[0]=0;
		int fres=fscanf(test_file, "%[^\r\n] ", temp_me);

		char* cur_temp_loc=temp_me;

		if(fres!=EOF)
		{
			ch=*cur_temp_loc++;
			cur_temp_loc++;
			id=GetUnsignedInteger(cur_temp_loc);
			cur_temp_loc++;
		}

		if(EOF==fres)
		{
			break;
		}

		if(ch=='r' && id==last_doc_id)
		{
			unsigned int num_res=GetUnsignedInteger(cur_temp_loc); cur_temp_loc++;

			fprintf(sol_file, "r %u %u", id, num_res);

			unsigned int qid;
			unsigned int* cur_results=(unsigned int*)malloc(num_res*sizeof(unsigned int));

			for(i=0;i<(int)num_res;i++)
			{
				qid=GetUnsignedInteger(cur_temp_loc); cur_temp_loc++;
				fprintf(sol_file, " %u", qid*10+rand()%10);
				cur_results[i]=qid;
			}
			fprintf(sol_file, "\n");
		}
		else
		{
			fprintf(sol_file, "%s\n", temp_me);
		}
	}

	v=GetClockTimeInMilliSec()-v;

	fclose(test_file);

	double throughput=(double)num_processed_docs*1000.0/v;

	fprintf(out_file, "Your program has successfully passed all tests.\n");
	fprintf(out_file, "Processed Documents = %u documents. ", num_processed_docs);
	//if(file_finished) fprintf(out_file, " (all documents are processed)\n");
	//else fprintf(out_file, " (not all documents are processed)\n");
	fprintf(out_file, "Time = "); PrintTime(v, out_file); fprintf(out_file, "\n");
	fprintf(out_file, "Throughput = %0.3lf documents/second.\n", throughput);

	fflush(out_file);

	fclose(sol_file);
}

///////////////////////////////////////////////////////////////////////////////////////////////

int main(int argc, char* argv[])
{
	FILE* out_file=stdout;

	if(argc>=3)
	{
		unsigned int last_doc_id=0;
		sscanf(argv[2], "%u", &last_doc_id);
		TestSigmod(argv[1], 0, out_file, last_doc_id);
	}
	else
	{
		TestSigmod("small_test.txt", 0, out_file, 528);
	}

	//if(argc<=1) TestSigmod("inter_test.txt", 0, out_file);
	//if(argc<=1) TestSigmod("large_test.txt", 0, out_file);
	//if(argc<=1) TestSigmod("huge_test.txt", 0, out_file);
	fflush(NULL);
	return 0;
}

///////////////////////////////////////////////////////////////////////////////////////////////

#endif
