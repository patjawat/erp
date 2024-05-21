SELECT person.HR_CID,i.DATE_TIME_REQUEST,i.YEAR_ID,
a.ARTICLE_NUM,
IF(a.ARTICLE_NUM > 1, "asset","general") as send_type,
i.SYMPTOM,
i.REPAIR_COMMENT,
i.TECH_RECEIVE_ID,
i.TECH_RECEIVE_NAME,
concat(i.TECH_RECEIVE_DATE,' ',i.TECH_RECEIVE_TIME) as accept_date,
concat(i.REPAIR_DATE,' ',i.REPAIR_TIME) as repair_date,
i.DEPARTMENT_SUB_ID,
dep.HR_DEPARTMENT_SUB_SUB_NAME,
i.STATUS,
i.REPAIR_SCORE,
(4) as status
FROM backoffice.informrepair_index i



INSERT INTO helpdesk (crated_date,year_budget)
SELECT e.user_id,person.HR_CID,i.DATE_TIME_REQUEST,i.YEAR_ID,
a.ARTICLE_NUM,
IF(a.ARTICLE_NUM > 1, "asset","general") as send_type,
i.SYMPTOM,
i.REPAIR_COMMENT,
i.TECH_RECEIVE_ID,
i.TECH_RECEIVE_NAME,
concat(i.TECH_RECEIVE_DATE,' ',i.TECH_RECEIVE_TIME) as accept_date,
concat(i.REPAIR_DATE,' ',i.REPAIR_TIME) as repair_date,
i.DEPARTMENT_SUB_ID,
dep.HR_DEPARTMENT_SUB_SUB_NAME,
i.STATUS,
i.REPAIR_SCORE
FROM backoffice.informrepair_index i

LEFT JOIN backoffice.asset_article a ON a.ARTICLE_ID = i.ARTICLE_ID
LEFT JOIN backoffice.hrd_person person ON person.ID = i.USER_REQUEST_ID
LEFT JOIN backoffice.hrd_department_sub_sub dep ON dep.HR_DEPARTMENT_SUB_SUB_ID = i.DEPARTMENT_SUB_ID
LEFT JOIN employees e ON e.cid = backoffice.person.HR_CID
WHERE a.ARTICLE_NUM IS NOT NULL
AND i.STATUS = "SUCCESS"
LIMIT 1;