SELECT DISTINCT {column} as name FROM (
    SELECT
        id,
        title,
        MAX(IF(name = 'providerPhone', value, NULL)) AS `providerPhone`,
        MAX(IF(name = 'providerEmail', value, NULL)) AS `providerEmail`,
        MAX(IF(name = 'providerWebsite', value, NULL)) AS `providerWebsite`,
        MAX(IF(name = 'providerAddress', value, NULL)) AS `providerAddress`,
        MAX(IF(name = 'providerCity', value, NULL)) AS `providerCity`,
        MAX(IF(name = 'providerDistrict', value, NULL)) AS `providerDistrict`,
        MAX(IF(name = 'providerCountry', value, NULL)) AS `providerCountry`,
        MAX(IF(name = 'providerLatitude', value, NULL)) AS `providerLatitude`,
        MAX(IF(name = 'providerLongitude', value, NULL)) AS `providerLongitude`,
        MAX(IF(name = 'providerFromDay1', value, NULL)) AS `providerFromDay1`,
        MAX(IF(name = 'providerToDay1', value, NULL)) AS `providerToDay1`,
        MAX(IF(name = 'providerOpeningHours1', value, NULL)) AS `providerOpeningHours1`,
        MAX(IF(name = 'providerClosingHours1', value, NULL)) AS `providerClosingHours1`,
        MAX(IF(name = 'providerFromDay2', value, NULL)) AS `providerFromDay2`,
        MAX(IF(name = 'providerEmergencyServices', value, NULL)) AS `providerEmergencyServices`,
        MAX(IF(name = 'providerEmergencyPhone', value, NULL)) AS `providerEmergencyPhone`,
        MAX(IF(name = 'providerDirectBilling', value, NULL)) AS `providerDirectBilling`,
        MAX(IF(name = 'providerOpeningHours2', value, NULL)) AS `providerOpeningHours2`,
        MAX(IF(name = 'providerClosingHours2', value, NULL)) AS `providerClosingHours2`,
        MAX(IF(name = 'providerAmount', value, NULL)) AS `providerAmount`,
        MAX(IF(name = 'MedicalType', value, NULL)) AS `MedicalType`,
        MAX(IF(name = 'providerMedicalServices', value, NULL)) AS `providerMedicalServices`,
        MAX(IF(name = 'providersPriceFrom', value, NULL)) AS `providersPriceFrom`,
        MAX(IF(name = 'providersPriceTo', value, NULL)) AS `providersPriceTo`,
        lang
    FROM (
        SELECT
            item.id,
            item.title,
            fd.name,
            fd.label,
            rel.value,
            SUBSTRING_INDEX(item.language, '-', 1) as lang
        FROM `gd68j_flexicontent_items_tmp` item
            JOIN `gd68j_flexicontent_fields_item_relations` rel
              ON item.id = rel.`item_id`
            JOIN gd68j_flexicontent_fields fd
              ON fd.id = rel.field_id
        WHERE item.access = 1
          AND item.type_id = 12
    ) A
    GROUP BY id, title
) A
{where}