<?php

namespace App\Http\Controllers;

use App\Models\BudgetTracking;
use App\Models\UserBudget;
use App\Models\BudgetHistory;
use App\Models\Product;
use App\Models\Order;
use App\Models\Bundle; // Added Bundle model
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetManagementController extends Controller
{
    protected function getCurrentTime()
    {
        return Carbon::now();
    }

    public function dashboard()
    {
        \Log::info('Starting dashboard method');
        
        $user = auth()->user();
        $now = $this->getCurrentTime();
        
        \Log::info('Current time in dashboard: ' . $now->toDateTimeString());
        
        // Get active budget tracking without status check
        $budgetTracking = BudgetTracking::where('user_id', $user->id)
            ->where('cycle_start_date', '<=', $now)
            ->where('cycle_end_date', '>', $now)
            ->orderBy('created_at', 'desc')
            ->first();
            
        \Log::info('Budget tracking query:', [
            'user_id' => $user->id,
            'current_time' => $now->toDateTimeString(),
            'found_tracking' => $budgetTracking ? 'yes' : 'no',
            'query_conditions' => [
                'cycle_start_before' => $now->toDateTimeString(),
                'cycle_end_after' => $now->toDateTimeString()
            ]
        ]);

        if ($budgetTracking) {
            \Log::info('Active budget found:', [
                'id' => $budgetTracking->id,
                'budget_id' => $budgetTracking->budget_id,
                'cycle_start' => $budgetTracking->cycle_start_date,
                'cycle_end' => $budgetTracking->cycle_end_date,
                'total_amount' => $budgetTracking->total_amount,
                'remaining_amount' => $budgetTracking->remaining_amount
            ]);
        } else {
            \Log::warning('No active budget found for user:', [
                'user_id' => $user->id,
                'check_time' => $now->toDateTimeString()
            ]);
            
            // Check if there's any budget tracking at all
            $anyBudget = BudgetTracking::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($anyBudget) {
                \Log::info('Found inactive budget:', [
                    'id' => $anyBudget->id,
                    'budget_id' => $anyBudget->budget_id,
                    'cycle_start' => $anyBudget->cycle_start_date,
                    'cycle_end' => $anyBudget->cycle_end_date,
                    'total_amount' => $anyBudget->total_amount
                ]);
            }
        }

        // Get budget history
        $budgetHistory = BudgetHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        \Log::info('Budget history count: ' . $budgetHistory->count());

        // Get recommended products within budget
        $recommendedProducts = collect();
        $recommendedBundles = collect();
        if ($budgetTracking) {
            // Query for products
            $productQuery = Product::where('status', 'approved')
                ->where('price', '<=', $budgetTracking->remaining_amount)
                ->where('user_id', '!=', $user->id)
                ->where('is_sold', false)
                ->where('quantity', '>', 0);

            // Query for bundles
            $bundleQuery = Bundle::where('status', 'approved')
                ->where('price', '<=', $budgetTracking->remaining_amount)
                ->where('user_id', '!=', $user->id)
                ->where('is_sold', false)
                ->where('quantity', '>', 0);

            // Apply category filter if selected
            if (request()->filled('category')) {
                $productQuery->where('category', request('category'));
                $bundleQuery->whereHas('categories', function($q) {
                    $q->where('category', request('category'));
                });
            }

            // Apply search filter if provided
            if (request()->filled('search')) {
                $searchTerm = request('search');
                $productQuery->where(function($q) use ($searchTerm) {
                    $q->where('product_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
                $bundleQuery->where(function($q) use ($searchTerm) {
                    $q->where('bundle_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            }

            // Get paginated results for both products and bundles
            $recommendedProducts = $productQuery->latest()->paginate(6)->withQueryString();
            $recommendedBundles = $bundleQuery->latest()->paginate(3)->withQueryString();
        }

        // Get unique categories from available products within budget
        $categories = Product::where('status', 'approved')
            ->where('is_sold', false)
            ->where('quantity', '>', 0)
            ->where('price', '<=', $budgetTracking ? $budgetTracking->remaining_amount : 0)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('buyer.budget.dashboard', compact(
            'budgetTracking',
            'budgetHistory',
            'recommendedProducts',
            'recommendedBundles',
            'categories'
        ));
    }

    public function index()
    {
        return $this->dashboard();
    }

    public function setBudget(Request $request)
    {
        try {
            \Log::info('Starting setBudget method');
            \Log::info('Request data:', $request->all());

            $request->validate([
                'budget_amount' => 'required|numeric|min:100',
                'cycle_type' => 'required|in:monthly,yearly',
            ], [
                'budget_amount.min' => 'The minimum budget amount must be at least LKR 100.'
            ]);

            \Log::info('Validation passed');

            $user = auth()->user();
            $now = $this->getCurrentTime();

            \Log::info('Current time: ' . $now->toDateTimeString());

            try {
                DB::beginTransaction();
                \Log::info('Starting database transaction');

                // Create or update user budget first
                $userBudget = UserBudget::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'budget_amount' => $request->budget_amount,
                        'cycle_type' => $request->cycle_type,
                        'start_date' => $now
                    ]
                );

                if (!$userBudget) {
                    throw new \Exception('Failed to create UserBudget record');
                }

                \Log::info('User budget created/updated:', [
                    'id' => $userBudget->id,
                    'budget_amount' => $userBudget->budget_amount,
                    'cycle_type' => $userBudget->cycle_type,
                    'start_date' => $userBudget->start_date
                ]);

                // Calculate cycle end date based on cycle type
                $cycleEndDate = $request->cycle_type === 'monthly' 
                    ? $now->copy()->addMonth() 
                    : $now->copy()->addYear();

                // Create new budget tracking
                $budgetTracking = BudgetTracking::create([
                    'user_id' => $user->id,
                    'budget_id' => $userBudget->id,
                    'total_amount' => $request->budget_amount,
                    'spent_amount' => 0,
                    'remaining_amount' => $request->budget_amount,
                    'cycle_start_date' => $now,
                    'cycle_end_date' => $cycleEndDate,
                    'last_modified_at' => $now
                ]);

                if (!$budgetTracking) {
                    throw new \Exception('Failed to create BudgetTracking record');
                }

                \Log::info('Budget tracking created:', [
                    'id' => $budgetTracking->id,
                    'total_amount' => $budgetTracking->total_amount,
                    'cycle_start' => $budgetTracking->cycle_start_date,
                    'cycle_end' => $budgetTracking->cycle_end_date
                ]);

                DB::commit();
                \Log::info('Transaction committed successfully');

                return redirect()->route('buyer.budget.index')
                    ->with('success', 'Budget has been set successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Error in database transaction:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error in setBudget:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while setting your budget. Please try again.');
        }
    }

    public function update(Request $request)
    {
        try {
            \Log::info('Starting update method');
            \Log::info('Request data:', $request->all());

            $request->validate([
                'budget_amount' => 'required|numeric|min:100',
                'cycle_type' => 'required|in:monthly,yearly',
            ], [
                'budget_amount.min' => 'The minimum budget amount must be at least LKR 100.'
            ]);

            \Log::info('Validation passed');

            $user = auth()->user();
            $now = $this->getCurrentTime();
            
            \Log::info('Current time: ' . $now->toDateTimeString());
            
            // Check existing budget tracking
            $currentTracking = BudgetTracking::where('user_id', $user->id)
                ->where('cycle_end_date', '>', $now)
                ->first();

            \Log::info('Current tracking query:', [
                'user_id' => $user->id,
                'current_time' => $now->toDateTimeString(),
                'found_tracking' => $currentTracking ? 'yes' : 'no'
            ]);

            DB::beginTransaction();
            try {
                // Update or create user budget preferences first
                $userBudget = UserBudget::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'budget_amount' => $request->budget_amount,
                        'cycle_type' => $request->cycle_type,
                        'start_date' => $now
                    ]
                );

                if ($currentTracking) {
                    // Check if enough time has passed since last modification
                    if (!$currentTracking->canModifyBudget()) {
                        DB::rollBack();
                        return back()->with('error', "You can only modify your budget once every 7 days. Please wait " . $currentTracking->getTimeUntilNextModification() . ".");
                    }

                    // Check if new budget is less than spent amount
                    if ($request->budget_amount < $currentTracking->spent_amount) {
                        DB::rollBack();
                        return back()->with('error', "New budget cannot be less than the amount you've already spent (LKR " . number_format($currentTracking->spent_amount, 2) . ")");
                    }

                    // Update the current tracking
                    $currentTracking->update([
                        'total_amount' => $request->budget_amount,
                        'remaining_amount' => $request->budget_amount - $currentTracking->spent_amount,
                        'last_modified_at' => $now
                    ]);

                    \Log::info('Updated existing budget tracking:', $currentTracking->toArray());
                } else {
                    // Calculate new cycle dates
                    $startDate = $now;
                    $endDate = $request->cycle_type === 'monthly' 
                        ? $now->copy()->addMonth() 
                        : $now->copy()->addYear();

                    // Create new tracking
                    $currentTracking = new BudgetTracking();
                    $currentTracking->user_id = $user->id;
                    $currentTracking->budget_id = $userBudget->id;
                    $currentTracking->total_amount = $request->budget_amount;
                    $currentTracking->spent_amount = 0;
                    $currentTracking->remaining_amount = $request->budget_amount;
                    $currentTracking->cycle_start_date = $startDate;
                    $currentTracking->cycle_end_date = $endDate;
                    $currentTracking->last_modified_at = $now;
                    $currentTracking->save();

                    \Log::info('Created new budget tracking:', $currentTracking->toArray());
                }

                DB::commit();
                return redirect()->route('buyer.budget.index')->with('success', 'Budget updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Budget update transaction failed:', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                return back()->with('error', 'An error occurred while updating your budget. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('Error in update:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return back()->with('error', 'An error occurred while updating your budget. Please try again.');
        }
    }
}
