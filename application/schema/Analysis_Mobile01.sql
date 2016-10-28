-- Table: "data-analysis"."Analysis_Mobile01"

-- DROP TABLE "data-analysis"."Analysis_Mobile01";

CREATE TABLE "data-analysis"."Analysis_Mobile01"
(
  id integer NOT NULL DEFAULT nextval('"data-analysis"."analysisMobile01_id_seq"'::regclass), -- 主鍵，自動遞增
  forums character varying(20), -- 討論區類別
  subject character varying(500), -- 主旨
  hot integer, -- 熱度
  reply integer, -- 回覆數
  authur character varying(50), -- 作者
  authur_date timestamp without time zone, -- 發文日
  latest_replay_date timestamp without time zone, -- 最後回文日
  mobile01_forums_code integer, -- Mobile01討論區類別代號
  mobile01_thread_code integer, -- Mobile01討論串代號
  CONSTRAINT "analysisMobile01_pkey" PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE "data-analysis"."Analysis_Mobile01"
  OWNER TO "data-analysis";
COMMENT ON TABLE "data-analysis"."Analysis_Mobile01"
  IS '分析資料表';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".id IS '主鍵，自動遞增';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".forums IS '討論區類別';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".subject IS '主旨';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".hot IS '熱度';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".reply IS '回覆數';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".authur IS '作者';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".authur_date IS '發文日';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".latest_replay_date IS '最後回文日';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".mobile01_forums_code IS 'Mobile01討論區類別代號';
COMMENT ON COLUMN "data-analysis"."Analysis_Mobile01".mobile01_thread_code IS 'Mobile01討論串代號';
