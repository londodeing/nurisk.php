TYPE=TRIGGERS
triggers='CREATE DEFINER=`root`@`localhost` TRIGGER tr_single_latest_assessment\n                BEFORE INSERT ON assessment_utama\n                FOR EACH ROW\n                BEGIN\n                    IF NEW.is_latest = 1 THEN\n                        UPDATE assessment_utama \n                        SET is_latest = 0 \n                        WHERE id_insiden = NEW.id_insiden AND is_latest = 1;\n                    END IF;\n                END'
sql_modes=1168113696
definers='root@localhost'
client_cs_names='utf8mb4'
connection_cl_names='utf8mb4_unicode_ci'
db_cl_names='latin1_swedish_ci'
created=1781714148484587
