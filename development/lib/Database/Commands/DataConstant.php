<?php
class ParameterNames
{
	private function __construct()
	{}
	
	const CURRENT_DATE = '@current_date';
	const CURRENT_SERIES_ID = '@currentSeriesId';
	
	const DATE_CREATED = '@dateCreated';
	const DATE_MODIFIED = '@dateModified';
	const DESCRIPTION = '@description';
	
	const END_DATE = '@endDate';
	const EMAIL_ADDRESS = '@email';
	
	const FIRST_NAME = '@fname';
	
	const GROUP = '@group';
		
	const HOMEPAGE_ID = '@homepageid';

	const IS_ACTIVE = '@isactive';
	
	const LAST_LOGIN = '@lastlogin';
	const LAST_NAME = '@lname';
	
	const ORGANIZATION = '@organization';
	
	const PASSWORD = '@password';
	const PHONE = '@phone';
	const POSITION = '@position';

	const REFERENCE_NUMBER = '@referenceNumber';
	
	const REPEAT_OPTIONS = '@repeatOptions';
	const REPEAT_TYPE = '@repeatType';
	
	const RESERVATION_INSTANCE_ID = '@reservationid';
	const RESERVATION_USER_LEVEL_ID = '@levelid';
	
	const RESOURCE_ID = '@resourceid';
	const RESOURCE_ALLOW_MULTIDAY = '@allow_multiday_reservations';
	const RESOURCE_AUTOASSIGN = '@autoassign';
	const RESOURCE_CONTACT = '@contact_info';
	const RESOURCE_COST = '@unit_cost';
	const RESOURCE_DESCRIPTION = '@description';
	const RESOURCE_LOCATION = '@location';
	const RESOURCE_MAX_PARTICIPANTS = '@max_participants';
	const RESOURCE_MAXDURATION = '@max_duration';
	const RESOURCE_MAXNOTICE = '@max_notice_time';
	const RESOURCE_MINDURATION = '@min_duration';
	const RESOURCE_MININCREMENT = '@min_increment';
	const RESOURCE_MINNOTICE = '@min_notice_time';
	const RESOURCE_NAME = '@resource_name';	
	const RESOURCE_NOTES = '@resource_notes';
	const RESOURCE_REQUIRES_APPROVAL = '@requires_approval';
	const RESOURCE_LEVEL_ID = "@resourceLevelId";
	
	
	const SALT = '@salt';
	const SCHEDULE_ID = '@scheduleid';
	const SERIES_ID = '@seriesid';
	const START_DATE = '@startDate';
	const STATUS_ID = '@statusid';
	const TIMEZONE_NAME = '@timezone';
	const TYPE_ID = '@typeid';
	const LANGUAGE = '@language';
	const TITLE = '@title';
	const USER_ID = '@userid';
	const USER_ROLE_ID = '@user_roleid';
	const USER_STATUS_ID = '@user_statusid';
	const USERNAME = '@username';	
	const FIRST_NAME_SETTING = '@fname_setting';
	const LAST_NAME_SETTING = '@lname_setting';
	const USERNAME_SETTING = '@username_setting';	
	const EMAIL_ADDRESS_SETTING = '@email_setting';
	const PASSWORD_SETTING = '@password_setting';
	const ORGANIZATION_SELECTION_SETTING = '@organization_setting';
	const GROUP_SETTING = '@group_setting';
	const POSITION_SETTING = '@position_setting';
	const ADDRESS_SETTING = '@address_setting';
	const PHONE_SETTING = '@phone_setting';
	const HOMEPAGE_SELECTION_SETTING = '@homepage_setting';
	const TIMEZONE_SELECTION_SETTING = '@timezone_setting';	
}

class Queries
{
	private function __construct()
	{}
    
    //MPinnegar
    //TO-DO: Put this in alphabetical order
    //Words following @symbols are variables that will be used by the SQL statement. In this case we will be taking in the userId and using that to find out the reservations that the person represented by the userId has
    //So the only symbol is the @userId. All of these can be found in the ParameterNames class above
    
    //Inside of the statement, besides using the funky @ symbol for variables, everything else is like a normal SQL statement. You use the table and column names from the SQL database.
    //The below constants will be used inside of Commands.php in the style of "parent::__construct(Queries::GET_ALL_RESERVATIONS_BY_USER)"
    //I am lame, and have not actually tested this. I need to populate my database and give this one a whirl. However, running it on the empty database was successful, so there aren't any simple syntax errors :)   
	const GET_ALL_RESERVATIONS_BY_USER = 
		'SELECT
			reservation_series.*
		FROM
			reservation_users JOIN reservation_series
		WHERE
			(@userid = reservation_users.user_id AND reservation_users.series_id = reservation_series.series_id)';
	
	const ADD_RESERVATION = 
		'INSERT INTO 
			reservation_instances (start_date, end_date, reference_number, series_id)
		VALUES (@startDate, @endDate, @referenceNumber, @seriesid)';
	
	const ADD_RESERVATION_RESOURCE =
		'INSERT INTO
			reservation_resources (series_id, resource_id, resource_level_id)
		VALUES (@seriesid, @resourceid, @resourceLevelId)';	
	
	const ADD_RESERVATION_SERIES = 
		'INSERT INTO 
			reservation_series (date_created, title, description, allow_participation, allow_anon_participation, repeat_type, repeat_options, schedule_id, type_id, status_id, owner_id)
		VALUES (@dateCreated, @title, @description, false, false, @repeatType, @repeatOptions, @scheduleid, @typeid, @statusid, @userid)';
	
	const ADD_RESERVATION_USER  = 
		'INSERT INTO
			reservation_users (reservation_instance_id, user_id, reservation_user_level)
		VALUES (@reservationid, @userid, @levelid)';
	
	const AUTO_ASSIGN_PERMISSIONS = 
		'INSERT INTO 
			user_resource_permissions (user_id, resource_id) 
		SELECT 
			@userid as user_id, resource_id 
		FROM 
			resources
		WHERE 
			autoassign=1';
	
	const CHECK_EMAIL = 
		'SELECT user_id 
		FROM users
		WHERE email = @email';
		
	const CHECK_USERNAME = 
		'SELECT user_id 
		FROM users
		WHERE username = @username';
		
	const CHECK_USER_EXISTANCE = 
		'SELECT user_id 
		FROM users
		WHERE (username = @username OR email = @email)';
		
	const COOKIE_LOGIN = 
		'SELECT user_id, lastlogin, email 
		FROM users 
		WHERE user_id = @userid';
	
	const DELETE_SERIES =
		'DELETE 
		FROM reservation_series
		WHERE series_id = @seriesid';
	
	const LOGIN_USER = 
		'SELECT user_id, email, fname, lname, timezone, lastlogin, homepageid
		FROM users 
		WHERE (username = @username OR email = @username)';
	
	const GET_ALL_SCHEDULES = 
		'SELECT * 
		FROM schedules';
	
	const GET_ALL_USERS_BY_STATUS = 
		'SELECT user_id, fname, lname, email, timezone, language
		FROM users
		WHERE status_id = @user_statusid';
		
	const GET_DASHBOARD_ANNOUNCEMENTS =
		'SELECT announcement_text 
		FROM announcements
		WHERE (start_datetime <= @current_date AND end_datetime >= @current_date)
		ORDER BY priority DESC';

	const GET_GROUP_RESOURCE_PERMISSIONS = 
		'SELECT 
			grp.group_id, r.resource_id, r.name
		FROM
			group_resource_permissions grp, resources r, user_groups ug
		WHERE
			ug.user_id = @userid AND ug.group_id = grp.group_id AND grp.resource_id = r.resource_id';
	
		
	const GET_RESOURCE_BY_ID = 
		'SELECT 
			r.*
		FROM 
			resources r
		WHERE
			r.isactive = 1';
	
	const GET_RESERVATION_BY_ID =
		'SELECT
			*
		FROM
			reservation_instances r
		INNER JOIN	
			reservation_series rs ON r.series_id = rs.series_id
		WHERE
			r.reservation_instance_id = @reservationid AND
			status_id <> 2';
	
	const GET_RESERVATION_FOR_EDITING = 
		'SELECT 
			* 
		FROM
			reservation_instances ri
		INNER JOIN
			reservation_series r 
			ON 
				r.series_id = ri.series_id
		INNER JOIN
			users u
			ON 
				u.user_id = r.owner_id
		INNER JOIN
			reservation_resources rr
			ON
				r.series_id = rr.series_id
			AND 
				rr.resource_level_id = @resourceLevelId
		WHERE 
				reference_number = @referenceNumber
			AND	
				r.status_id <> 2';
	
	const GET_RESERVATION_LIST = 
		'SELECT
			*
		FROM
			reservation_instances ri
		INNER JOIN
			reservation_series rs ON ri.series_id = rs.series_id
		INNER JOIN
			reservation_resources rr ON rr.series_id = rs.series_id
		INNER JOIN
			reservation_users ru ON ru.reservation_instance_id = ri.reservation_instance_id
		INNER JOIN
			resources r on rr.resource_id = r.resource_id
		WHERE 
			ri.start_date >= @startDate AND
			ri.start_date <= @endDate AND
			ru.user_id = @userid AND
			ru.reservation_user_level = @levelid AND
			rs.status_id <> 2
		ORDER BY 
			ri.start_date ASC';
	
	const GET_RESERVATION_PARTICIPANTS =
		'SELECT
			u.user_id, 
			u.fname,
			u.lname,
			u.email,
			ru.*
		FROM
			reservation_users ru
		INNER JOIN 
			users u
			ON
				ru.user_id = u.user_id
		WHERE
			reservation_instance_id = @reservationid';
	
	const GET_RESERVATION_RESOURCES =
		'SELECT
			r.resource_id, r.name, rr.resource_level_id
		FROM
			reservation_resources rr
		INNER JOIN 
			resources r
			ON
			rr.resource_id = r.resource_id
		WHERE
			rr.series_id = @seriesid';
	
	const GET_RESERVATION_SERIES_INSTANCES =
		'SELECT 
			*
		FROM
			reservation_instances
		WHERE 
			series_id = @seriesid';
	
	// TODO: Pass in "Deleted" status ID
	const GET_RESERVATIONS_COMMAND =
	 'SELECT 
		ri.reservation_instance_id,
		ri.start_date,
		ri.end_date,
		r.type_id,
		r.status_id,
		r.description,
		rs.resource_id,
		u.user_id,
		u.fname,
		u.lname,
		ri.series_id,
		ri.reference_number
	FROM 
		reservation_instances ri
	INNER JOIN
		reservation_series r ON ri.series_id = r.series_id AND r.status_id <> 2
	INNER JOIN 
		reservation_users ru ON ri.reservation_instance_id = ru.reservation_instance_id
	INNER JOIN 
		users u ON ru.user_id = u.user_id
	INNER JOIN 
		reservation_resources rr ON rr.series_id = ri.series_id
	INNER JOIN 
		resource_schedules rs ON rs.resource_id = rr.resource_id
	WHERE 
		ru.reservation_user_level = 1 AND
		(rs.schedule_id = @scheduleid OR @scheduleid = -1) AND	
		(
			(ri.start_date >= @startDate AND ri.start_date <= @endDate)
			OR
			(ri.end_date >= @startDate AND ri.end_date <= @endDate)
			OR
			(ri.start_date <= @startDate AND ri.end_date >= @endDate)
		)';
	
//	(ri.start_date >= @startDate AND ri.start_date <= @endDate)
//	OR
//	(ri.end_date >= @startDate AND ri.end_date <= @endDate)
//	OR
//	(ri.start_date <= @startDate AND ri.end_date >= @endDate)
//	
	const GET_SCHEDULE_TIME_BLOCK_GROUPS = 
		'SELECT 
			tb.label, 
			tb.end_label, 
			tb.start_time, 
			tb.end_time, 
			tb.availability_code,
			tbg.timezone
		FROM 
			time_blocks tb, 
			time_block_groups tbg, 
			schedule_time_block_groups stbg
		WHERE 
			tbg.block_groupid = stbg.block_group_id AND 
			tb.block_group_id = tbg.block_groupid AND
			stbg.schedule_id = @scheduleid 
		ORDER BY tb.start_time';
	
	const GET_SCHEDULE_RESOURCES = 
		'SELECT 
			r.*
		FROM 
			resources r, resource_schedules rs 
		WHERE 
			r.resource_id = rs.resource_id AND 
			rs.schedule_id = @scheduleid AND
			r.isactive = 1';
	
	const GET_USER_BY_ID = 
		'SELECT
			email, fname, lname, timezone, language, position, phone, homepageid, status_id
		FROM
			users
		WHERE
			user_id = @userid';
	
	const GET_USER_EMAIL_PREFERENCES =
		'SELECT 
			*
		FROM
			user_email_preferences
		WHERE
			user_id = @userid';
	
	const GET_USER_RESOURCE_PERMISSIONS = 
		'SELECT 
			urp.user_id, r.resource_id, r.name
		FROM
			user_resource_permissions urp, resources r
		WHERE
			urp.user_id = @userid AND r.resource_id = urp.resource_id';
		
	const GET_USER_ROLES = 
		'SELECT 
			user_id, user_level 
		FROM 
			roles r, user_roles ur
		WHERE 
			ur.user_id = @userid AND r.roleid = ur.role_id';
	
	const MIGRATE_PASSWORD = 
		'UPDATE 
			users 
		SET 
			password = @password, legacypassword = null, salt = @salt 
		WHERE 
			user_id = @userid';

	const REGISTER_FORM_SETTINGS = 
		'INSERT INTO
			registration_form_settings (fname_setting, lname_setting, username_setting, email_setting, password_setting, 
			organization_setting, group_setting, position_setting, address_setting, phone_setting, homepage_setting, timezone_setting)	
		VALUES
			(@fname_setting, @lname_setting, @username_setting, @email_setting, @password_setting, @organization_setting, 
			 @group_setting, @position_setting, @address_setting, @phone_setting, @homepage_setting, @timezone_setting)
		';

	const REGISTER_MINI_USER = 
		'INSERT INTO 
			users (email, password, fname, lname, username, salt, timezone, status_id, role_id)
		VALUES
			(@email, @password, @fname, @lname, @username, @salt, @timezone, @user_statusid, @user_roleid)
		';

	const REGISTER_USER = 
		'INSERT INTO 
			users (email, password, fname, lname, phone, organization_id, position, username, salt, timezone, language, homepageid, status_id)
		VALUES
			(@email, @password, @fname, @lname, @phone, @organization, @position, @username, @salt, @timezone, @language, @homepageid, @user_statusid)
		';

	const REMOVE_RESERVATION_INSTANCE = 
		'DELETE FROM
			reservation_instances
		WHERE
			reference_number = @referenceNumber';

	const ADD_RESOURCE = 
		'INSERT INTO 
			resources (name, location, contact_info, description, notes, isactive, min_duration, min_increment, 
					   max_duration, unit_cost, autoassign, requires_approval, allow_multiday_reservations, 
					   max_participants, min_notice_time, max_notice_time)
		VALUES
			(@resource_name, @location, @contact_info, @description, @resource_notes, @isactive, @min_duration, @min_increment, 
			 @max_duration, @unit_cost, @autoassign, @requires_approval, @allow_multiday_reservations,
		     @max_participants, @min_notice_time, @max_notice_time)
		';
	
	const UPDATE_LOGINTIME = 
		'UPDATE 
			users 
		SET 
			lastlogin = @lastlogin 
		WHERE 
			user_id = @userid';

	const UPDATE_FUTURE_RESERVATION_INSTANCES =
		'UPDATE 
			reservation_instances
		SET
			series_id = @seriesid
		WHERE
			series_id = @currentSeriesId AND
			start_date >= (SELECT start_date FROM reservation_instances WHERE reference_number = @referenceNumber)';
	
	const UPDATE_RESERVATION_INSTANCE = 
		'UPDATE
			reservation_instances
		SET
			series_id = @seriesid,
			start_date = @startDate,
			end_date = @endDate
		WHERE
			reference_number = @referenceNumber';
	
	const UPDATE_RESERVATION_SERIES = 
		'UPDATE
			reservation_series
		SET
			last_modified = @dateModified, 
			title = @title, 
			description = @description, 
			repeat_type = @repeatType, 
			repeat_options = @repeatOptions
		WHERE
			series_id = @seriesid';
	
	const UPDATE_USER_BY_USERNAME = 
		'UPDATE 
			users 
		SET 
			email = @email,
			password = @password,
			salt = @salt,
			fname = @fname,
			lname = @lname,
			phone = @phone,
			organization_id = @organization,
			position = @position
		WHERE 
			username = @username';
	
	const VALIDATE_USER = 
		'SELECT user_id, password, salt, legacypassword
		FROM users 
		WHERE (username = @username OR email = @username)';
}

class ColumnNames
{
	private function __construct()
	{}
	
	// USERS //
	const USER_ID = 'user_id';	
	const USERNAME = 'username';	
	const EMAIL = 'email';
	const FIRST_NAME = 'fname';
	const LAST_NAME = 'lname';	
	const PASSWORD = 'password';
	const OLD_PASSWORD = 'legacypassword';
	const USER_CREATED = 'date_created';
	const USER_MODIFIED = 'last_modified';
	const ROLE_ID = 'role_id';
	const USER_STATUS_ID = 'status_id';
	const HOMEPAGE_ID = 'homepageid';
	const LAST_LOGIN = 'lastlogin';
	const TIMEZONE_NAME = 'timezone';
	const LANGUAGE_CODE = 'language';
	const SALT = 'salt';

	// USER_ORGANIZATIONS //
	const ORGANIZATION_ID = 'organization_id';

	// USER_ADDRESSES //
	const ADDRESS_ID = 'address_id';

	// USER_LONG_QUOTAS //
	const USER_LQUOTA_ID = 'long_quota_id';

	// USER_DAY_QUOTAS //
	const USER_DQUOTA_ID = 'day_quota_id';
	
	// ROLES //
	const USER_LEVEL = 'user_level';
	
	// ANNOUNCEMENTS //
	const ANNOUNCEMENT_TEXT = 'announcement_text';
	
	// GROUPS //
	const GROUP_ID = 'groupid';
	
	// TIME BLOCKS //
	const BLOCK_LABEL = 'label';
	const BLOCK_LABEL_END = 'end_label';
	const BLOCK_CODE = 'availability_code';
	const BLOCK_TIMEZONE = 'timezone';

	// TIME BLOCK USES //
	const BLOCK_START = 'start_time';
	const BLOCK_END = 'end_time';	

	// RESERVATION SERIES //	
	const RESERVATION_USER = 'user_id';
	const RESERVATION_GROUP = 'group_id';
	const RESERVATION_CREATED = 'date_created';
	const RESERVATION_MODIFIED = 'last_modified';
	const RESERVATION_TYPE = 'type_id';
	const RESERVATION_TITLE = 'title';
	const RESERVATION_DESCRIPTION = 'description';
	const RESERVATION_COST = 'total_cost';
	const RESERVATION_PARENT_ID = 'parent_id';	
	const REPEAT_TYPE = 'repeat_type';
	const REPEAT_OPTIONS = 'repeat_options';
	const RESERVATION_STATUS = 'status_id';
	const SERIES_ID = 'series_id';
	
	// RESERVATION_INSTANCE //
	const RESERVATION_INSTANCE_ID = 'reservation_instance_id';
	const RESERVATION_START = 'start_date';
	const RESERVATION_END = 'end_date';
	const REFERENCE_NUMBER = 'reference_number';
	
	// RESERVATION_USER //
	const RESERVATION_OWNER = 'reservation_owner';
	const RESERVATION_USER_LEVEL = 'reservation_user_level';
	
	// RESOURCE //
	const RESOURCE_ID = 'resource_id';
	const RESOURCE_NAME = 'name';
	const RESOURCE_LOCATION = 'location';
	const RESOURCE_CONTACT = 'contact_info';
	const RESOURCE_DESCRIPTION = 'description';
	const RESOURCE_NOTES = 'notes';
	const RESOURCE_MINDURATION = 'min_duration';
	const RESOURCE_MININCREMENT = 'min_increment';
	const RESOURCE_MAXDURATION = 'max_duration';
	const RESOURCE_COST = 'unit_cost';
	const RESOURCE_AUTOASSIGN = 'autoassign';
	const RESOURCE_REQUIRES_APPROVAL = 'requires_approval';
	const RESOURCE_ALLOW_MULTIDAY = 'allow_multiday_reservations';
	const RESOURCE_MAX_PARTICIPANTS = 'max_participants';
	const RESOURCE_MINNOTICE = 'min_notice_time';
	const RESOURCE_MAXNOTICE = 'max_notice_time';

	// RESERVATION RESOURCES
	const RESOURCE_LEVEL_ID = 'resource_level_id';
	
	// SCHEDULE //
	const SCHEDULE_ID = 'schedule_id';
	const SCHEDULE_NAME = 'name';
	const SCHEDULE_DEFAULT = 'isdefault';
	const SCHEDULE_WEEKDAY_START = 'weekdaystart';
	const SCHEDULE_DAYS_VISIBLE = 'daysvisible';
	
	// EMAIL PREFERENCES //
	const EVENT_CATEGORY = 'event_category';
	const EVENT_TYPE= 'event_type';
	
	const REPEAT_START = 'repeat_start';
	const REPEAT_END = 'repeat_end';
	
	

}
?>