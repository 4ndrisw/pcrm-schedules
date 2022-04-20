<?php

namespace app\services\schedules;

use app\services\AbstractKanban;

class SchedulesPipeline extends AbstractKanban
{
    protected function table()
    {
        return 'schedules';
    }

    public function defaultSortDirection()
    {
        return get_option('default_schedules_pipeline_sort_type');
    }

    public function defaultSortColumn()
    {
        return get_option('default_schedules_pipeline_sort');
    }

    public function limit()
    {
        return get_option('schedules_pipeline_limit');
    }

    protected function applySearchQuery($q)
    {
        if (!startsWith($q, '#')) {
            $fields_client    = $this->ci->db->list_fields(db_prefix() . 'clients');
            $fields_schedules = $this->ci->db->list_fields(db_prefix() . 'schedules');

            $q = $this->ci->db->escape_like_str($q);

            $where = '(';
            $i     = 0;
            foreach ($fields_client as $f) {
                $where .= db_prefix() . 'clients.' . $f . ' LIKE "%' . $q . '%" ESCAPE \'!\'';
                $where .= ' OR ';
                $i++;
            }
            $i = 0;
            foreach ($fields_schedules as $f) {
                $where .= db_prefix() . 'schedules.' . $f . ' LIKE "%' . $q . '%" ESCAPE \'!\'';
                $where .= ' OR ';

                $i++;
            }
            $where = substr($where, 0, -4);
            $where .= ')';
            $this->ci->db->where($where);
        } else {
            $this->ci->db->where(db_prefix() . 'schedules.id IN
                (SELECT rel_id FROM ' . db_prefix() . 'taggables WHERE tag_id IN
                (SELECT id FROM ' . db_prefix() . 'tags WHERE name="' . $this->ci->db->escape_str(strafter($search, '#')) . '")
                AND ' . db_prefix() . 'taggables.rel_type=\'schedule\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
        }

        return $this;
    }

    protected function initiateQuery()
    {
        $has_permission_view = has_permission('schedules', '', 'view');
        $noPermissionQuery   = get_schedules_where_sql_for_staff(get_staff_user_id());

        $this->ci->db->select(db_prefix() . 'schedules.id,status,invoiceid,' . get_sql_select_client_company() . ',total,currency,symbol,' . db_prefix() . 'currencies.name as currency_name,date,expirydate,clientid');
        $this->ci->db->from('schedules');
        $this->ci->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'schedules.clientid', 'left');
        $this->ci->db->join(db_prefix() . 'currencies', db_prefix() . 'schedules.currency = ' . db_prefix() . 'currencies.id');
        $this->ci->db->where('status', $this->status);

        if (!$has_permission_view) {
            $this->ci->db->where($noPermissionQuery);
        }

        return $this;
    }
}
