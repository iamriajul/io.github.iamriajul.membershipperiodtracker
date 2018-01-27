<form class="CRM_MembershipPeriodTracker_Page_MembershipPeriodHistory">
<div id="MembershipPeriodHistoryTab" class="view-content">
    <div class="help">
        Membership Period History Tracking functionality added by <a href="https://github.com/iamriajul/io.github.iamriajul.membershipperiodtracker">io.github.iamriajul.membershipperiodtracker(Membership Period Tracker)</a> extension.<br>
        You don't need to do any configuration, it will automatically keep track of all Membership creating, renewing since you installed.<br>
        To see Membership Period History for all Contact please refer to this link: <a href="{crmURL p='civicrm/contact/view/membershipperiodhistory' q='reset=1'}">Membership Period History for All Contact</a>
    </div>

    {include file="CRM/common/pager.tpl" location="top"}
    {if $rows}
    {strip}
        <table class="selector row-highlight">
            <thead class="sticky">
            <tr>
                <th>
                    <a href="{$sort_by_membership_type_link}" class="crm-pager-link {$sort_by_membership_type_class}">
                        {ts}Type{/ts}
                    </a>
                </th>
                <th>
                    <a href="{$sort_by_start_date_link}" class="crm-pager-link {$sort_by_start_date_class}">
                        {ts}Start Date{/ts}
                    </a>
                </th>
                <th>
                    <a href="{$sort_by_end_date_link}" class="crm-pager-link {$sort_by_end_date_class}">
                        {ts}Expire Date{/ts}
                    </a>
                </th>
                <th>
                    <a href="{$sort_by_contribution_link}" class="crm-pager-link {$sort_by_contribution_class}">
                        {ts}Contribution{/ts}
                    </a>
                </th>
                <th>{ts}Membership{/ts}</th>
            </tr>
            </thead>
            <tbody>

            {foreach from=$rows item=row}
                <tr id="membership_period_{$row.id}" class="{cycle values="odd-row,even-row"} membership-period">
                    <td class="membership-period-membership_type" data-order="{$row.membership_type}">{$row.membership_type}</td>
                    <td class="membership-period-start_date" data-order="{$row.start_date}">{$row.start_date|crmDate}</td>
                    <td class="membership-period-end_date" data-order="{$row.end_date}">{$row.end_date|crmDate}</td>
                    <td class="membership-period-contribution" data-order="{$row.contribution_id}">
                        {if $row.contribution_id}
                            <a href="{crmURL p='civicrm/contact/view/contribution' q="reset=1&id=`$row.contribution_id`&cid=`$row.contact_id`&action=view&context=contribution&selectedChild=membershipperiod"}" title="{ts}View Contribution{/ts}" class="crm-hover-button crm-popup action-item">{ts}View{/ts}</a>
                        {else}
                            {ts}Not Recorded{/ts}
                        {/if}
                    </td>
                    <td class="membership-period-membership">
                        {if $row.membership_id}
                            <a href="{crmURL p='civicrm/contact/view/membership' q="reset=1&id=`$row.membership_id`&cid=`$row.contact_id`&action=view&context=membership&selectedChild=membershipperiod"}" title="{ts}View Membership{/ts}" class="crm-hover-button crm-popup action-item">{ts}View{/ts}</a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/strip}
    {else}
        <div class="messages status no-popup">
            <div class="icon inform-icon"></div>
            No membership period history have been recorded for this contact yet.
        </div>
    {/if}

    {include file="CRM/common/pager.tpl" location="bottom" }
</div>
</form>