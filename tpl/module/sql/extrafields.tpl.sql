-- <Extrafields table>
-- Copyright (C) <${current_year}>  <${author_name}>

CREATE TABLE llx_${table_name} (
    rowid INTEGER AUTO_INCREMENT PRIMARY KEY,
    tms TIMESTAMP,
    fk_object INTEGER NOT NULL,
    import_key VARCHAR(14)
) ENGINE=innodb;
