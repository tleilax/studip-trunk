CREATE OR REPLACE PROCEDURE SP_CHECK_SERVER_NONCE
(
P_CONSUMER_KEY                 IN        VARCHAR2,
P_TOKEN                        IN        VARCHAR2,
P_TIMESTAMP                    IN        NUMBER,
P_MAX_TIMESTAMP_SKEW           IN        NUMBER,
P_NONCE                        IN        VARCHAR2,
P_RESULT                       OUT       NUMBER
)
AS
 
 -- PROCEDURE TO Check an nonce/timestamp combination.  Clears any nonce combinations
 -- that are older than the one received.
V_IS_MAX                           NUMBER;
V_MAX_TIMESTAMP                    NUMBER;
V_IS_DUPLICATE_TIMESTAMP           NUMBER;

V_EXC_INVALID_TIMESTAMP            EXCEPTION;
V_EXC_DUPLICATE_TIMESTAMP          EXCEPTION;
BEGIN

  P_RESULT := 0;

  -- removed in Appendix A of RFC 5849
  -- BEGIN 
  --   SELECT MAX(OSN_TIMESTAMP), 
  --   CASE 
  --        WHEN MAX(OSN_TIMESTAMP) > (P_TIMESTAMP + P_MAX_TIMESTAMP_SKEW) THEN 1 ELSE 0 
  --   END "IS_MAX" INTO V_MAX_TIMESTAMP, V_IS_MAX
  --   FROM OAUTH_SERVER_NONCE
  --   WHERE OSN_CONSUMER_KEY = P_CONSUMER_KEY
  --   AND OSN_TOKEN        = P_TOKEN;
  --   
  --   IF V_IS_MAX = 1 THEN
  --      RAISE V_EXC_INVALID_TIMESTAMP;
  --   END IF;
  --     
  -- EXCEPTION
  -- WHEN NO_DATA_FOUND THEN
  --      NULL;
  -- END;        
  
  BEGIN
  SELECT 1 INTO V_IS_DUPLICATE_TIMESTAMP FROM DUAL WHERE EXISTS
  (SELECT OSN_ID FROM OAUTH_SERVER_NONCE 
    WHERE OSN_CONSUMER_KEY = P_CONSUMER_KEY
    AND OSN_TOKEN = P_TOKEN
    AND OSN_TIMESTAMP = P_TIMESTAMP
    AND OSN_NONCE = P_NONCE);
    
    IF V_IS_DUPLICATE_TIMESTAMP = 1 THEN
       RAISE V_EXC_DUPLICATE_TIMESTAMP;
    END IF;
  EXCEPTION
  WHEN NO_DATA_FOUND THEN
    NULL;
  END;          
  
  -- Insert the new combination
  INSERT INTO OAUTH_SERVER_NONCE
  (OSN_ID, OSN_CONSUMER_KEY, OSN_TOKEN, OSN_TIMESTAMP, OSN_NONCE)
  VALUES
  (SEQ_OSN_ID.NEXTVAL, P_CONSUMER_KEY, P_TOKEN, P_TIMESTAMP, P_NONCE);

  -- Clean up all timestamps older than the one we just received
  DELETE FROM OAUTH_SERVER_NONCE
  WHERE OSN_CONSUMER_KEY	= P_CONSUMER_KEY
  AND OSN_TOKEN			= P_TOKEN
  AND OSN_TIMESTAMP     < (P_TIMESTAMP - P_MAX_TIMESTAMP_SKEW);
                        

EXCEPTION
WHEN V_EXC_INVALID_TIMESTAMP THEN
P_RESULT := 2; -- INVALID_TIMESTAMP
WHEN V_EXC_DUPLICATE_TIMESTAMP THEN
P_RESULT := 3; -- DUPLICATE_TIMESTAMP
WHEN OTHERS THEN
-- CALL THE FUNCTION TO LOG ERRORS
ROLLBACK;
P_RESULT := 1; -- ERROR
END;
/
