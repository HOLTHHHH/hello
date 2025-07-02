<?php

require_once '../config/db.php'; // 假设这个文件包含数据库连接配置
require_once '../api/sing.php'; // 假设这个文件包含API调用函数

// 函数：处理歌曲请求
function retrievingSongs($request) {
    $carmi = $request->post('carmi');
    $type = $request->post('type');

    if (!$carmi || !$type) {
        return json(['status' => 0, 'msg' => '参数不能为空']);
    }

    $result = userIdSql($carmi);
    if (empty($result)) {
        return json(['status' => 0, 'msg' => '暂无歌曲 请点击联系客服更新']);
    }

    $singId = $result[0]['singId'];
    $cookie = $result[0]['cookie'];
    $userId = $result[0]['userId'];

    try {
        if ($type == 1) {
            $response = delCloud($singId, $cookie);
            if ($response['status'] == 1) {
                return json(['status' => 1, 'msg' => '删除云盘歌曲成功']);
            } else {
                return json(['status' => 0, 'msg' => '删除云盘歌曲失败']);
            }
        } elseif ($type == 2) {
            $allSing = alldelCloudApi($cookie);
            $singlist = $allSing['data']['data'];
            $promises = implode(',', array_column($singlist, 'simpleSong.id'));
            $delResponse = delCloud($promises, $cookie);
            if ($delResponse['status'] == 1) {
                $userBlackResponse = userBlack($carmi, 1);
                if ($userBlackResponse) {
                    return json(['status' => 1, 'msg' => '清空云盘歌曲成功']);
                } else {
                    return json(['status' => 0, 'msg' => '清空云盘歌曲失败（拉黑失败）']);
                }
            } else {
                return json(['status' => 0, 'msg' => '清空云盘歌曲失败（删除失败）']);
            }
        } elseif ($type == 3) {
            $userBlackResponse = userBlack($carmi, 0);
            if ($userBlackResponse) {
                return json(['status' => 1, 'msg' => '解除拉黑成功']);
            } else {
                return json(['status' => 0, 'msg' => '解除拉黑失败']);
            }
        } elseif ($type == 4) {
            // 制裁
            $paramsId = [
                'uid' => $userId,
                'cookie' => $cookie
            ];
            $b = songSheetList($paramsId);
            $playlistId = implode(',', array_column($b['data']['playlist'], 'id'));
            $ids = [
                'playlist' => $playlistId,
                'cookie' => $cookie,
                'name' => '白嫖狗'
            ];
            $delSongResponse = delSongSheetList($ids);
            $addSheetResponse = addSheet($ids);
            $setUserResponse = setUser($ids);
            if ($delSongResponse && $addSheetResponse && $setUserResponse) {
                return json(['status' => 1, 'msg' => '已制裁此用户']);
            } else {
                return json(['status' => 0, 'msg' => '制裁用户失败']);
            }
        } else {
            return json(['status' => 0, 'msg' => '无效的操作类型']);
        }
    } catch (Exception $e) {
        return json(['status' => 0, 'msg' => '系统出错了~']);
    }
}

// 函数：更新用户信息
function setUser($data) {
    try {
        $response = setUserApi($data);
        return $response;
    } catch (Exception $e) {
        return ['status' => 0, 'msg' => $e->getMessage()];
    }
}

// 函数：创建歌单
function addSheet($data) {
    try {
        $response = addSheetListApi($data);
        return $response;
    } catch (Exception $e) {
        return ['status' => 0, 'msg' => $e->getMessage()];
    }
}

// 函数：获取用户ID和Cookie
function userIdSql($carmi) {
    $sql = "SELECT * FROM upload_record WHERE carmi=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("s", $carmi);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return $result;
}

// 函数：删除用户歌单
function delSongSheetList($data) {
    try {
        $response = delSongSheetListApi($data);
        return $response;
    } catch (Exception $e) {
        return ['status' => 0, 'msg' => $e->getMessage()];
    }
}

// 函数：获取用户歌单
function songSheetList($data) {
    try {
        $response = songSheetListApi($data);
        return $response;
    } catch (Exception $e) {
        return ['status' => 0, 'msg' => $e->getMessage()];
    }
}

// 函数：拉黑
function userBlack($carmi, $type) {
    $sql = "UPDATE upload_record SET status=? WHERE carmi=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("is", $type, $carmi);
    $success = $stmt->execute();
    return $success;
}

// 函数：删除云盘歌曲
function delCloud($singId, $cookie) {
    $dataList = [
        'singId' => $singId,
        'cookie' => $cookie
    ];
    $response = delCloudApi($dataList);
    if ($response['status'] != 1) {
        throw new Exception('歌曲回收失败');
    }
    return ['status' => 1];
}

// 函数：检查卡密状态
function statusCarmi($request) {
    $carmi = $request->post('carmi');

    if (!$carmi) {
        return json(['status' => 0, 'msg' => '卡密不能为空']);
    }

    $result = statusUser($carmi);
    if (empty($result)) {
        return json(['status' => 0, 'msg' => '卡密无效']);
    }

    return json(['status' => 1, 'data' => $result]);
}

// 函数：获取卡密状态
function statusUser($carmi) {
    $sql = "SELECT * FROM carmi WHERE carmi=?";
    $stmt = $GLOBALS['db']->prepare($sql);
    $stmt->bind_param("s", $carmi);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return $result;
}

// 辅助函数：发送JSON响应
function json($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// 示例：如何使用（假设你有一个请求处理库，如Slim）
// $app->post('/retrievingSongs', function ($request, $response, $args) {
//     return retrievingSongs($request);
// });
// $app->post('/statusCarmi', function ($request, $response, $args) {
//     return statusCarmi($request);
// });

?>