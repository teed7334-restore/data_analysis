-- Table: "data-analysis"."Analysis_Mobile01_Log"

-- DROP TABLE "data-analysis"."Analysis_Mobile01_Log";

CREATE TABLE "data-analysis"."Analysis_Mobile01_Log"
(
  id integer NOT NULL DEFAULT nextval('"data-analysis"."Analysis_Mobile01_log_id_seq"'::regclass), -- 主鍵，自動遞增
  forums character varying(20), -- 討論區類別
  status_code integer, -- 抓取失敗代碼
  run_at timestamp without time zone, -- 執行時間
  CONSTRAINT "Analysis_Mobile01_log_pkey" PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "data-analysis"."Analysis_Mobile01_Log"
  OWNER TO "data-analysis";
COMMENT ON TABLE "data-analysis"."Analysis_Mobile01_Log"
  IS '爬Mobile01失敗日誌';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01_Log".id IS '主鍵，自動遞增';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01_Log".forums IS '討論區類別';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01_Log".status_code IS '抓取失敗代碼';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01_Log".run_at IS '執行時間';
