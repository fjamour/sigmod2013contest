--  Copyright (C) 2013 by
--  Fuad Jamour <fjamour@gmail.com>
--  All rights reserved.
--  MIT license.

CREATE TABLE  _RESULTS_TBL_NAME(
	team_name           VARCHAR(64), 
	timestamp           int,
	res_str             VARCHAR(32768),
    std_out             VARCHAR(_RES_STR_SZ),
    std_err             VARCHAR(_RES_STR_SZ),
    mklib_out           TEXT,
    PRIMARY KEY(team_name, timestamp));
