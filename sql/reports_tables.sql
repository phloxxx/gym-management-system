-- Make sure we have appropriate indexes for efficient report generation

-- Index on member join date for time-based reports
ALTER TABLE member ADD INDEX idx_member_joined_date (JOINED_DATE);

-- Index on member program ID for filtering
ALTER TABLE member ADD INDEX idx_member_program_id (PROGRAM_ID);

-- Index on member subscription ID for filtering
ALTER TABLE member ADD INDEX idx_member_sub_id (SUB_ID);

-- Index on member active status for filtering
ALTER TABLE member ADD INDEX idx_member_is_active (IS_ACTIVE);

-- Indexes for transactions table
ALTER TABLE transaction ADD INDEX idx_transaction_member_id (MEMBER_ID);
ALTER TABLE transaction ADD INDEX idx_transaction_date (TRANSAC_DATE);
ALTER TABLE transaction ADD INDEX idx_transaction_type (TRANSAC_TYPE);
ALTER TABLE transaction ADD INDEX idx_transaction_date_type (TRANSAC_DATE, TRANSAC_TYPE);

-- Make sure we have appropriate foreign keys
ALTER TABLE member 
    ADD CONSTRAINT fk_member_program
    FOREIGN KEY (PROGRAM_ID) REFERENCES program(PROGRAM_ID)
    ON DELETE RESTRICT;

ALTER TABLE member 
    ADD CONSTRAINT fk_member_subscription
    FOREIGN KEY (SUB_ID) REFERENCES subscription(SUB_ID)
    ON DELETE RESTRICT;

ALTER TABLE transaction 
    ADD CONSTRAINT fk_transaction_member
    FOREIGN KEY (MEMBER_ID) REFERENCES member(MEMBER_ID)
    ON DELETE RESTRICT;
