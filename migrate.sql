-- a table for storing files in SQL as blobs (Binary Large Object)
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    data LONGBLOB NOT NULL
);

-- Replace the trigger declared in the previous SQL script
DROP TRIGGER `check_pointSystemCategoryID`;

DELIMITER $$
CREATE TRIGGER `check_pointSystemCategoryID` BEFORE INSERT ON `event` FOR EACH ROW BEGIN
    DECLARE categorySystemID INT;

    -- Get the pointSystemID of the categorySystemID being inserted
    SELECT pointSystemID INTO categorySystemID FROM pointsystemcategory WHERE pointSystemCategoryID = NEW.pointSystemCategoryID;

    -- Check if the pointSystemID is equal to 1
    IF categorySystemID != 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'pointSystemID must be 1';
    END IF;

    -- event.eventDescription may be null, but since text datatypes
    -- cannot have default values in MySQL, we have to use triggers
    -- to set the default value upon INSERT
    IF NEW.eventDescription IS NULL THEN
    	SET NEW.eventDescription = '';
    END IF;
END
$$
DELIMITER ;

-- event.eventPhoto is non-nullable in the previous SQL script
-- and yet it can be passed without any value. Thus, we should
-- set a default value to it.
ALTER TABLE event
MODIFY COLUMN eventPhoto VARCHAR(255) DEFAULT '';

-- same case as the president, adviser, and chairperson columns
ALTER TABLE event
MODIFY COLUMN president VARCHAR(100) DEFAULT '';

ALTER TABLE event
MODIFY COLUMN adviser VARCHAR(100) DEFAULT '';

ALTER TABLE event
MODIFY COLUMN chairperson VARCHAR(100) DEFAULT '';

-- `pin` and `profilePicture` can still be null since
-- they are only specified after account creation
ALTER TABLE employeeuser
MODIFY COLUMN pin VARCHAR(255) DEFAULT NULL;

ALTER TABLE employeeuser
MODIFY COLUMN profilePicture VARCHAR(255) DEFAULT NULL;

ALTER TABLE studentuser
MODIFY COLUMN pin VARCHAR(255) DEFAULT NULL;

ALTER TABLE studentuser
MODIFY COLUMN profilePicture VARCHAR(255) DEFAULT NULL;

-- accommodate an extra column for holding references to blobs
ALTER TABLE studentuser
ADD profilePictureFileReference INT DEFAULT NULL;

ALTER TABLE studentuser
ADD FOREIGN KEY (profilePictureFileReference) REFERENCES files (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- we have created a table called `files` which stores files in form of blobs.
-- thus, we can simply set organizationLogo as nullable
ALTER TABLE organization
MODIFY COLUMN organizationLogo VARCHAR(255) DEFAULT NULL;

-- then add a column for the logo, whose values should be reference to the files table
ALTER TABLE organization
ADD logoFileReference INT DEFAULT NULL;

ALTER TABLE organization
ADD FOREIGN KEY (logoFileReference) REFERENCES files (id) ON DELETE CASCADE ON UPDATE CASCADE;

-- response should be nullable in feedbackresponse,
-- since it is optionally defined
ALTER TABLE feedbackresponse
MODIFY COLUMN response text DEFAULT NULL;
