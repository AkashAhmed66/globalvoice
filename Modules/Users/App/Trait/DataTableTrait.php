<?php

namespace Modules\Users\App\Trait;

trait DataTableTrait
{
  public function getTableHeader($tableName = ''): array
  {
    $headers = [
      'user-group-list' => [
        "id" => "#",
        'name' => "Name",
        'action' => 'Action'
      ],
      'user-list' => [
        'id' => "#",
        'full_name' => 'Full Name',
        'status' => 'Status',
        'name' => 'User Name',
        'email' => 'Email',
        'mobile' => 'Mobile',
        'group' => 'User Group',
        'action' => 'Action'
      ],
      'reseller-list' => [
        'id' => "#",
        'reseller_name' => 'Name',
        'address' => 'Address',
        'sms_rate' => 'SMS Rate',
        'available_balance' => 'Available Balance',
        'due' => 'Due',
        'phone' => 'Phone',
        'tps' => 'TPS',
        'status' => 'Status',
        'action' => 'Manage'
      ],
      'balance-list' => [
        "id" => "#",
        'name' => "Client Name",
        'balance_amount' => "Balance",
      ],
      'cdr-list' => [
        "id" => "#",
        'name' => "Name",
        'action' => 'Action'
      ],
      'client-list' => [
        "id" => "#",
        'name' => "Name",
        'status' => "Status",
        'contact_name' => "Contact Name",
        'contact_no' => "Number",
        'mail' => "Mail",
        'district' => "District",
        'action' => 'Action'
      ],
      				
      'mnp-list' => [
        "id" => "#",
        'number' => "Number",
        'recipientRC' => "RecipientRC",
        'donorRC' => "DonorRC",
        'nrhRC' => "NrhRC",
        'donorRC' => "DonorRC",
        'created_date' => "Ported Date",
      ],
      'number-list' => [
        "id" => "#",
        'no' => "Number",
        'type' => "Type",
        'client_id' => "Client",
        'is_booked' => "Is Booked",
        'channel' => "Channel",
        'did_balance' => "DID Balance",
        'amount' => "Amount",
        'status' => "Status",
        'action' => 'Action'
      ],
      'tarif-list' => [
        "id" => "#",
        'name' => "Name",
        'pulse_local' => "Local Pulse",
        'action' => 'Action'
      ],
    ];

    return $headers[$tableName] ?? [];
  }
}
