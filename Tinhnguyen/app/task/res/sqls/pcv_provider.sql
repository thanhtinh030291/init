SELECT * FROM (
    SELECT
        id,
        title AS `name`,
        MAX(IF(name = 'providerPhone', value, NULL)) AS `phone`,
        MAX(IF(name = 'providerEmail', value, NULL)) AS `email`,
        MAX(IF(name = 'providerWebsite', value, NULL)) AS `website`,
        MAX(IF(name = 'providerAddress', value, NULL)) AS `address`,
        MAX(IF(name = 'providerCity', value, NULL)) AS `city`,
        MAX(IF(name = 'providerDistrict', value, NULL)) AS `district`,
        MAX(IF(name = 'providerCountry', value, NULL)) AS `country`,
        MAX(IF(name = 'providerLatitude', value, NULL)) AS `latitude`,
        MAX(IF(name = 'providerLongitude', value, NULL)) AS `longitude`,
        MAX(IF(name = 'providerFromDay1', value, NULL)) AS `day_from_1`,
        MAX(IF(name = 'providerToDay1', value, NULL)) AS `day_to_1`,
        MAX(IF(name = 'providerFromDay2', value, NULL)) AS `day_from_2`,
        MAX(IF(name = 'providerToDay2', value, NULL)) AS `day_to_2`,
        MAX(IF(name = 'providerOpeningHours1', value, NULL)) AS `hour_open_1`,
        MAX(IF(name = 'providerClosingHours1', value, NULL)) AS `hour_close_1`,
        MAX(IF(name = 'providerOpeningHours2', value, NULL)) AS `hour_open_2`,
        MAX(IF(name = 'providerClosingHours2', value, NULL)) AS `hour_close_2`,
        MAX(IF(name = 'providerEmergencyServices', value, NULL)) AS `emergency_services`,
        MAX(IF(name = 'providerEmergencyPhone', value, NULL)) AS `emergency_phone`,
        MAX(IF(name = 'providerDirectBilling', value, NULL)) AS `direct_billing`,
        MAX(IF(name = 'providerAmount', value, NULL)) AS `amount`,
        MAX(IF(name = 'MedicalType', value, NULL)) AS `medical_type`,
        MAX(IF(name = 'providerMedicalServices', value, NULL)) AS `medical_services`,
        MAX(IF(name = 'providersPriceFrom', value, NULL)) AS `price_from`,
        MAX(IF(name = 'providersPriceTo', value, NULL)) AS `price_to`
    FROM (
        SELECT
            item.id,
            item.title,
            fd.name,
            fd.label,
            rel.value
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