<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\BaseRepository;

/**
 * Class UserService
 * @package App\Services
 */
class UserRepository extends BaseRepository
{
    protected $model;

    public function __construct(
        User $model
    ){
        $this->model = $model;
    }
    
    public function userPagination(
        array $column = ['*'], 
        array $condition = [], 
        int $perPage = 1,
        array $extend = [],
        array $orderBy = ['id', 'DESC'],
        array $join = [],
        array $relations = [],
    ){

        $query = $this->model->select($column)->where(function($query) use ($condition){
            if(isset($condition['keyword']) && !empty($condition['keyword'])){
                // Chuoi OR nay PHAI nam trong mot closure rieng.
                // Viet thang `where(a)->orWhere(b)->where(c)` thi SQL hieu la
                // `a OR (b AND c)` - moi bo loc dat sau chuoi OR deu bi vo hieu
                // khi tu khoa khop. Loi nay co san tu truoc va chi lo ra khi
                // nguoi dung vua nhap tu khoa vua chon bo loc.
                $query->where(function($q) use ($condition){
                    $q->where('name', 'LIKE', '%'.$condition['keyword'].'%')
                      ->orWhere('email', 'LIKE', '%'.$condition['keyword'].'%')
                      ->orWhere('address', 'LIKE', '%'.$condition['keyword'].'%')
                      ->orWhere('phone', 'LIKE', '%'.$condition['keyword'].'%');
                });
            }
            if(isset($condition['publish']) && $condition['publish'] != 0){
                $query->where('publish', '=', $condition['publish']);
            }
            // Loc theo nhom thanh vien. O chon nay truoc day chi co hai lua chon
            // viet cung ("Quan tri vien", id 1) va KHONG he duoc dung o truy van -
            // chon gi cung ra ca danh sach.
            if(!empty($condition['user_catalogue_id'])){
                $query->where('user_catalogue_id', '=', $condition['user_catalogue_id']);
            }
            return $query;
        })->with('user_catalogues');
        if(!empty($join)){
            $query->join(...$join);
        }

        return $query->paginate($perPage)
                    ->withQueryString()->withPath(env('APP_URL').$extend['path']);
    }
}
