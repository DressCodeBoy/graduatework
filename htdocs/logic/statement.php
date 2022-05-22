<?php
class Statement
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function readAllStatementSubjects()
    {
        $sbjct = $this->conn->prepare("SELECT subject_description FROM statement_subjects;");
        $sbjct->execute();
        return $sbjct;
    }

    function readAllMeasureUnit()
    {
        $unit = $this->conn->prepare("SELECT unit_name FROM measure_unit;");
        $unit->execute();
        return $unit;
    }

    function readAllnmpVariants()
    {
        $variants = $this->conn->prepare("SELECT nmp_var_desc FROM nmp_variants;");
        $variants->execute();
        return $variants;
    }

    function readAllpurchaseVariants()
    {
        $variants = $this->conn->prepare("SELECT purch_var_desc FROM purchase_variants;");
        $variants->execute();
        return $variants;
    }

    function sendStatement($id_user, $statement_date, $statement_subject, $purchase_purpose, $product_name, $okpd2, $product_count, $unit, $price1_for1, $price1, $price2_for1, $price2, $price3_for1, $price3, $warranty_period, $final_price1, $final_price2, $final_price3, $nmp_price, $nmp_variants, $nmp_method, $source_finance, $ext, $purchase_variants, $delivery_period, $delivery_place, $supplier_determ, $file_nmp, $file_spec)
    {
        // добавление данных в основтую таблицу заявки
        $this->conn->beginTransaction();
        $add_statement = $this->conn->prepare("INSERT INTO statements (id_user, statement_date, purchase_purpose, NMP, NMP_method, source_finance, EXT, delivery_period, supplier_determ) VALUES (:id_user, :statement_date, :purchase_purpose, :NMP, :NMP_method, :source_finance, :EXT, :delivery_period, :supplier_determ);");
        $add_statement->bindParam('id_user', $id_user, PDO::PARAM_INT);
        // может быть надо изменить параметр
        $add_statement->bindParam('statement_date', $statement_date, PDO::PARAM_STR);
        $add_statement->bindParam('purchase_purpose', $purchase_purpose, PDO::PARAM_STR);
        $add_statement->bindParam('NMP', $nmp_price, PDO::PARAM_STR);
        $add_statement->bindParam('NMP_method', $nmp_method, PDO::PARAM_STR);
        $add_statement->bindParam('source_finance', $source_finance, PDO::PARAM_STR);
        $add_statement->bindParam('EXT', $ext, PDO::PARAM_STR);
        $add_statement->bindParam('delivery_period', $delivery_period, PDO::PARAM_INT);
        $add_statement->bindParam('supplier_determ', $supplier_determ, PDO::PARAM_STR);
        $add_statement->execute();
        $statement_id = $this->conn->lastInsertId();
        // добавление данных в таблицу условий оплаты
        $get_purch_variant_id = $this->conn->prepare("SELECT purch_var_id FROM purchase_variants WHERE purch_var_desc=:purch_var_desc;");
        $get_purch_variant_id->bindParam('purch_var_desc', $purchase_variants, PDO::PARAM_STR);
        $get_purch_variant_id->execute();
        $tmp_purch_variant_id = $get_purch_variant_id->fetch(PDO::FETCH_ASSOC);
        $purch_variant_id = $tmp_purch_variant_id['purch_var_id'];
        $add_purch_var_statement = $this->conn->prepare("INSERT INTO purch_var_statements (statement_id, purch_var_id) VALUES (:statement_id, :purch_var_id);");
        $add_purch_var_statement->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_purch_var_statement->bindParam('purch_var_id', $purch_variant_id, PDO::PARAM_INT);
        $add_purch_var_statement->execute();
        // добавление данных в таблицу места доставки
        if($delivery_place) {
        $add_delivery_place = $this->conn->prepare("INSERT INTO delivery_place (deliv_place_desc) VALUES (:deliv_place_desc);");
        $add_delivery_place->bindParam('deliv_place_desc', $delivery_place);
        $add_delivery_place->execute();
        $delivery_place_id = $this->conn->lastInsertId();
        $add_delivery_place_statement = $this->conn->prepare("INSERT INTO delivery_place_statement (statement_id, deliv_place_id) VALUES (:statement_id, :deliv_place_id);");
        $add_delivery_place_statement->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_delivery_place_statement->bindParam('deliv_place_id', $delivery_place_id, PDO::PARAM_INT);
        $add_delivery_place_statement->execute();
        }
        // добавление данных в таблицу предмет договора
        $get_subject_id = $this->conn->prepare("SELECT subject_id FROM statement_subjects WHERE subject_description=:subject_description;");
        $get_subject_id->bindParam('subject_description', $statement_subject, PDO::PARAM_STR);
        $get_subject_id->execute();
        $tmp_subject_id = $get_subject_id->fetch(PDO::FETCH_ASSOC);
        $subject_id = $tmp_subject_id['subject_id'];
        $add_sbjct_of_sttmnt = $this->conn->prepare("INSERT INTO sbj_of_sttmnt (statement_id, subject_id) VALUES (:statement_id, :subject_id);");
        $add_sbjct_of_sttmnt->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_sbjct_of_sttmnt->bindParam('subject_id', $subject_id, PDO::PARAM_INT);
        $add_sbjct_of_sttmnt->execute();
        // добавление данных в таблицу "что включает в себя НМЦ"
        $get_nmp_variant_id = $this->conn->prepare("SELECT nmp_var_id FROM nmp_variants WHERE nmp_var_desc=:nmp_var_desc;");
        $get_nmp_variant_id->bindParam('nmp_var_desc', $nmp_variants, PDO::PARAM_STR);
        $get_nmp_variant_id->execute();
        $tmp_nmp_variant_id = $get_nmp_variant_id->fetch(PDO::FETCH_ASSOC);
        $nmp_variant_id = $tmp_nmp_variant_id['nmp_var_id'];
        $add_nmp_var_statement = $this->conn->prepare("INSERT INTO nmp_var_statement (statement_id, nmp_var_id) VALUES (:statement_id, :nmp_var_id);");
        $add_nmp_var_statement->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_nmp_var_statement->bindParam('nmp_var_id', $nmp_variant_id, PDO::PARAM_INT);
        $add_nmp_var_statement->execute();
        // добавление данных в таблицу вложенных файлов
        $add_file = $this->conn->prepare("INSERT INTO attachments (attach_path1, attach_path2) VALUES (:attach_path1, :attach_path2);");
        $add_file->bindParam('attach_path1', $file_nmp, PDO::PARAM_STR);
        $add_file->bindParam('attach_path2', $file_spec, PDO::PARAM_STR);
        $add_file->execute();
        $file_id = $this->conn->lastInsertId();
        $add_attach_statement = $this->conn->prepare("INSERT INTO attach_statement (statement_id, attach_id) VALUES (:statement_id, :attach_id);");
        $add_attach_statement->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_attach_statement->bindParam('attach_id', $file_id, PDO::PARAM_INT);
        $add_attach_statement->execute();
        // добавление данных в таблицы описания покупки
        $get_measure_unit_id = $this->conn->prepare("SELECT unit_id FROM measure_unit WHERE unit_name=:unit_name;");
        $get_measure_unit_id->bindParam('unit_name', $unit, PDO::PARAM_STR);
        $get_measure_unit_id->execute();
        $tmp_measure_unit_id = $get_measure_unit_id->fetch(PDO::FETCH_ASSOC);
        $measure_unit_id = $tmp_measure_unit_id['unit_id'];
        $add_purch_desc_statement = $this->conn->prepare("INSERT INTO purch_desc_statement (statement_id, final_price1, final_price2, final_price3) VALUES (:statement_id, :final_price1, :final_price2, :final_price3);");
        $add_purch_desc_statement->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_purch_desc_statement->bindParam('final_price1', $final_price1, PDO::PARAM_STR);
        $add_purch_desc_statement->bindParam('final_price2', $final_price2, PDO::PARAM_STR);
        $add_purch_desc_statement->bindParam('final_price3', $final_price3, PDO::PARAM_STR);
        $add_purch_desc_statement->execute();
        $purch_desc_statement_id = $this->conn->lastInsertId();
        $add_purch_desc_row = $this->conn->prepare("INSERT INTO purch_desc_row (purch_desc_statement_id, product_name, okpd2, product_count, unit_id, price1_for1, price1, price2_for1, price2, price3_for1, price3, warranty_period) VALUES (:purch_desc_statement_id, :product_name, :okpd2, :product_count, :unit_id, :price1_for1, :price1, :price2_for1, :price2, :price3_for1, :price3, :warranty_period);");
        $add_purch_desc_row->bindParam('purch_desc_statement_id', $purch_desc_statement_id, PDO::PARAM_INT);
        $add_purch_desc_row->bindParam('product_name', $product_name, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('okpd2', $okpd2, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('product_count', $product_count, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('unit_id', $measure_unit_id, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('price1_for1', $price1_for1, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('price1', $price1, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('price2_for1', $price2_for1, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('price2', $price2, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('price3_for1', $price3_for1, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('price3', $price3, PDO::PARAM_STR);
        $add_purch_desc_row->bindParam('warranty_period', $warranty_period, PDO::PARAM_STR);
        $add_purch_desc_row->execute();
        // добавление данных в таблицу утверждения заявки
        $add_agreed_statement = $this->conn->prepare("INSERT INTO agreed_statement(statement_id) VALUES (:statement_id);");
        $add_agreed_statement->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $add_agreed_statement->execute();

        $this->conn->commit();
    }

    function searchUserStatements($user_id)
    {
        //выбор всех заявок пользователя
        $get_selection_user_statements_id = $this->conn->prepare("SELECT statement_id FROM statements WHERE id_user=:id_user;");
        $get_selection_user_statements_id->bindParam('id_user', $user_id, PDO::PARAM_INT);
        $get_selection_user_statements_id->execute();
        $tmp_selection_user_statements_id = $get_selection_user_statements_id->fetchAll(PDO::FETCH_ASSOC);
        $selection_user_statements_id = implode(',', array_map(function($tmp_selection_user_statements_id) {
            return $tmp_selection_user_statements_id['statement_id'];
        }, $tmp_selection_user_statements_id));
        //выбор данных этапов согласования заявок
        $select_user_agreed_statements = $this->conn->prepare("SELECT st.statement_date, ast.step1, ast.step1_date, ast.step1_comment, ast.step2, ast.step2_date, ast.step2_comment, ast.step3, ast.step3_date, ast.step3_comment, ast.step4, ast.step4_date, ast.step4_comment, ast.step5, ast.step5_date, ast.step5_comment, ast.step6, ast.step6_date, ast.step6_comment FROM agreed_statement AS ast JOIN statements AS st ON ast.statement_id=st.statement_id WHERE find_in_set(cast(ast.statement_id as char),:statement_id);");
        $select_user_agreed_statements->bindParam('statement_id', $selection_user_statements_id, PDO::PARAM_STR);
        $select_user_agreed_statements->execute();
        $agreed_statements = $select_user_agreed_statements->fetchAll(PDO::FETCH_ASSOC);
        return $agreed_statements;
    }

    function showStatementsForDepartmentHead($user_department)
    {
        $get_statements_id = $this->conn->prepare("SELECT statement_id FROM statements AS st JOIN users AS us ON st.id_user = us.user_id JOIN departments AS dep ON us.user_department = dep.department_id WHERE dep.department_desc =:user_department;");
        $get_statements_id->bindParam('user_department', $user_department);
        $get_statements_id->execute();
        $statements_id = $get_statements_id->fetchAll(PDO::FETCH_ASSOC);
        $statements_id = implode(',', array_map(function ($statements_id) {
            return $statements_id['statement_id'];
        }, $statements_id));
        $get_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE find_in_set(cast(st.statement_id as char),:statements_id) AND  ags.step1 = 0 AND ags.step1_comment IS NULL");
        $get_statement_info->bindParam('statements_id', $statements_id, PDO::PARAM_STR);
        $get_statement_info->execute();
        return $get_statement_info;
    }

    function showStatementsForViceRector($user_department)
    {
        $get_statements_id = $this->conn->prepare("SELECT statement_id FROM statements AS st JOIN users AS us ON st.id_user = us.user_id JOIN departments AS dep ON us.user_department = dep.department_id WHERE dep.department_desc =:user_department;");
        $get_statements_id->bindParam('user_department', $user_department);
        $get_statements_id->execute();
        $statements_id = $get_statements_id->fetchAll(PDO::FETCH_ASSOC);
        $statements_id = implode(',', array_map(function ($statements_id) {
            return $statements_id['statement_id'];
        }, $statements_id));
        $get_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE find_in_set(cast(st.statement_id as char),:statements_id) AND  ags.step1 = 1 AND ags.step2 = 0 AND ags.step2_comment IS NULL");
        $get_statement_info->bindParam('statements_id', $statements_id, PDO::PARAM_STR);
        $get_statement_info->execute();
        return $get_statement_info;
    }

    function showStatementsForFeuHead()
    {
        $get_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE ags.step2 = 1 AND ags.step3 = 0 AND ags.step3_comment IS NULL");
        $get_statement_info->execute();
        return $get_statement_info;
    }

    function showStatementsForAccountantHead()
    {
        $get_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE ags.step3 = 1 AND ags.step4 = 0 AND ags.step4_comment IS NULL");
        $get_statement_info->execute();
        return $get_statement_info;
    }

    function showStatementsForUKZHead()
    {
        $get_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE ags.step4 = 1 AND ags.step5 = 0 AND ags.step5_comment IS NULL");
        $get_statement_info->execute();
        return $get_statement_info;
    }

    function showStatementsForRector()
    {
        $get_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE ags.step5 = 1 AND ags.step6 = 0 AND ags.step6_comment IS NULL");
        $get_statement_info->execute();
        return $get_statement_info;
    }

    function addApprovalAndComment($approval_step, $is_approval, $comment, $statement_id) {
        $date = date('Y-m-d');
        switch ($approval_step) {
            case 1:
                $add_items = $this->conn->prepare("UPDATE agreed_statement SET step1=:is_approval, step1_comment=:comment, step1_date=:current_date WHERE statement_id =:statement_id");
                $add_items->bindParam('is_approval', $is_approval, PDO::PARAM_BOOL);
                $add_items->bindParam('comment', $comment, PDO::PARAM_STR);
                $add_items->bindParam('current_date', $date, PDO::PARAM_STR);
                $add_items->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
                $add_items->execute();
                break;
            case 2:
                $add_items = $this->conn->prepare("UPDATE agreed_statement SET step2=:is_approval, step2_comment=:comment, step2_date=:current_date WHERE statement_id =:statement_id");
                $add_items->bindParam('is_approval', $is_approval, PDO::PARAM_BOOL);
                $add_items->bindParam('comment', $comment, PDO::PARAM_STR);
                $add_items->bindParam('current_date', $date, PDO::PARAM_STR);
                $add_items->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
                $add_items->execute();
                break;
            case 3:
                $add_items = $this->conn->prepare("UPDATE agreed_statement SET step3=:is_approval, step3_comment=:comment, step3_date=:current_date WHERE statement_id =:statement_id");
                $add_items->bindParam('is_approval', $is_approval, PDO::PARAM_BOOL);
                $add_items->bindParam('comment', $comment, PDO::PARAM_STR);
                $add_items->bindParam('current_date', $date, PDO::PARAM_STR);
                $add_items->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
                $add_items->execute();
                break;
            case 4:
                $add_items = $this->conn->prepare("UPDATE agreed_statement SET step4=:is_approval, step4_comment=:comment, step4_date=:current_date WHERE statement_id =:statement_id");
                $add_items->bindParam('is_approval', $is_approval, PDO::PARAM_BOOL);
                $add_items->bindParam('comment', $comment, PDO::PARAM_STR);
                $add_items->bindParam('current_date', $date, PDO::PARAM_STR);
                $add_items->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
                $add_items->execute();
                break;
            case 5:
                $add_items = $this->conn->prepare("UPDATE agreed_statement SET step5=:is_approval, step5_comment=:comment, step5_date=:current_date WHERE statement_id =:statement_id");
                $add_items->bindParam('is_approval', $is_approval, PDO::PARAM_BOOL);
                $add_items->bindParam('comment', $comment, PDO::PARAM_STR);
                $add_items->bindParam('current_date', $date, PDO::PARAM_STR);
                $add_items->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
                $add_items->execute();
                break;
            case 6:
                $add_items = $this->conn->prepare("UPDATE agreed_statement SET step6=:is_approval, step6_comment=:comment, step6_date=:current_date WHERE statement_id =:statement_id");
                $add_items->bindParam('is_approval', $is_approval, PDO::PARAM_BOOL);
                $add_items->bindParam('comment', $comment, PDO::PARAM_STR);
                $add_items->bindParam('current_date', $date, PDO::PARAM_STR);
                $add_items->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
                $add_items->execute();
                break;
        }
    }

    function searchHeadEmail($user_department_id) {
        $get_email = $this->conn->prepare("SELECT email FROM users WHERE user_department = :user_department AND department_head = 1");
        $get_email->bindParam('user_department', $user_department_id, PDO::PARAM_INT);
        $get_email->execute();
        $email = $get_email->fetch(PDO::FETCH_ASSOC);
        $email = $email['email'];
        return $email;
    }

    function searchWorkerEmail($statement_id) {
        $get_user_email = $this->conn->prepare("SELECT us.email FROM users AS us WHERE us.user_id=(SELECT id_user FROM statements WHERE statement_id=:statement_id)");
        $get_user_email->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $get_user_email->execute();
        $user_email = $get_user_email->fetch(PDO::FETCH_ASSOC);
        $user_email = $user_email['email'];
        return $user_email;
    }

    function getAllStatementInfo($statement_id) {
        $get_all_statement_info = $this->conn->prepare("SELECT st.statement_id, st.statement_date, us.user_name, dep.department_desc, ss.subject_description, st.purchase_purpose, pdr.purch_desc_row_id,
        pdr.product_name, pdr.okpd2, pdr.product_count, mu.unit_name, pdr.price1_for1, pdr.price1, pdr.price2_for1, pdr.price2,  pdr.price3_for1,
        pdr.price3, warranty_period, pds.final_price1, pds.final_price2, pds.final_price3, st.NMP, nv.nmp_var_desc, st.NMP_method, st.source_finance,
        st.EXT, pv.purch_var_desc, st.delivery_period, dp.deliv_place_desc, st.supplier_determ, att.attach_path1, att.attach_path2, ags.step1,
        ags.step1_date, ags.step1_comment, ags.step2, ags.step2_date, ags.step2_comment, ags.step3, ags.step3_date, ags.step3_comment, ags.step4,
        ags.step4_date, ags.step4_comment, ags.step5, ags.step5_date, ags.step5_comment, ags.step6, ags.step6_date, ags.step6_comment
        FROM statements AS st
        JOIN users AS us ON st.id_user = us.user_id
        JOIN departments AS dep ON us.user_department = dep.department_id
        JOIN attach_statement AS ats ON st.statement_id = ats.statement_id
        JOIN attachments AS att ON ats.attach_id = att.attach_id
        JOIN sbj_of_sttmnt AS sos ON st.statement_id = sos.statement_id
        JOIN statement_subjects AS ss ON sos.subject_id = ss.subject_id
        JOIN purch_var_statements AS pvs ON st.statement_id = pvs.statement_id
        JOIN purchase_variants AS pv ON pvs.purch_var_id = pv.purch_var_id
        JOIN purch_desc_statement AS pds ON st.statement_id = pds.statement_id
        JOIN purch_desc_row AS pdr ON pds.purch_desc_statement_id = pdr.purch_desc_statement_id
        JOIN measure_unit AS mu ON pdr.unit_id = mu.unit_id
        JOIN delivery_place_statement AS dps ON st.statement_id = dps.statement_id
        JOIN delivery_place AS dp ON dps.deliv_place_id = dp.deliv_place_id
        JOIN nmp_var_statement AS nvs ON st.statement_id = nvs.statement_id
        JOIN nmp_variants AS nv ON nvs.nmp_var_id = nv.nmp_var_id
        JOIN agreed_statement AS ags ON st.statement_id = ags.statement_id
        WHERE st.statement_id = :statement_id");
        $get_all_statement_info->bindParam('statement_id', $statement_id, PDO::PARAM_INT);
        $get_all_statement_info->execute();
        return $get_all_statement_info;
    }
}
