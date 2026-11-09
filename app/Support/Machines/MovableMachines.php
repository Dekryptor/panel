<?php

namespace App\Support\Machines;

use App\Models\Machine;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class MovableMachines
{
    /** @return Collection<int, Machine> */
    public static function for(User $user): Collection
    {
        $workspaces = $user->workspaces()->get()
            ->filter(fn (Workspace $workspace) => Gate::forUser($user)->allows('manageMachines', $workspace));

        return Machine::query()
            ->whereIn('workspace_id', $workspaces->modelKeys())
            ->with('workspace:id,name,slug')
            ->orderBy('workspace_id')->orderBy('name')
            ->get();
    }
}
