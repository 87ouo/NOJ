<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/home', '/', 301);
Route::redirect('/acmhome/welcome.do', '/', 301);
Route::get('/acmhome/problemdetail.do','MainController@oldRedirect')->name('old.redirect');
Route::get('/opensearch.xml', function () {
    return response(getOpenSearchXML(), 200)->header("Content-type","text/xml");
});

Route::get('/', 'MainController@home')->middleware('contest_account')->name('home');

Route::get('/search', 'SearchController')->middleware('auth')->name('search');

Route::group(['prefix' => 'message','as' => 'message.'], function () {
    Route::get('/', 'MessageController@index')->middleware('auth')->name('index');
    Route::get('/{id}', 'MessageController@detail')->middleware('auth')->name('detail');
});

Route::group(['prefix' => 'account'], function () {
    Route::get('/', 'AccountController@index')->name('account_index');
    Route::get('/dashboard', 'AccountController@dashboard')->middleware('auth')->name('account_dashboard');
    Route::get('/settings', 'AccountController@settings')->middleware('auth')->name('account_settings');
});

Route::group(['prefix' => 'oauth', 'namespace' => 'OAuth', 'as' => 'oauth.'], function () {
    Route::group(['prefix' => 'github', 'as' => 'github.'], function () {
        Route::get('/', 'GithubController@redirectTo')->name('index');
        Route::get('/unbind','GithubController@unbind')->name('unbind');
        Route::get('/unbind/confirm','GithubController@confirmUnbind')->name('unbind.confirm');
        Route::get('/callback', 'GithubController@handleCallback')->name('callback');
    });
});

Route::group(['prefix' => 'user'], function () {
    Route::redirect('/', '/', 301);
    Route::get('/{uid}', 'UserController@view')->middleware('contest_account')->name('user_view');
});

Route::group(['prefix' => 'problem'], function () {
    Route::get('/', 'ProblemController@index')->middleware('contest_account')->name('problem_index');
    Route::get('/{pcode}', 'ProblemController@detail')->middleware('contest_account')->name('problem_detail');
    Route::get('/{pcode}/editor', 'ProblemController@editor')->middleware('auth', 'contest_account')->name('problem_editor');
    Route::get('/{pcode}/solution', 'ProblemController@solution')->middleware('auth', 'contest_account')->name('problem_solution');
    Route::get('/{pcode}/discussion', 'ProblemController@discussion')->middleware('auth', 'contest_account')->name('problem.discussion');
});

Route::get('/discussion/{dcode}', 'ProblemController@discussionPost')->middleware('auth', 'contest_account')->name('problem.discussion.post');

Route::get('/status', 'StatusController@index')->middleware('contest_account')->name('status_index');

Route::group(['namespace' => 'Group', 'prefix' => 'group','as' => 'group.'], function () {
    Route::get('/', 'IndexController@index')->middleware('contest_account')->name('index');
    Route::get('/create', 'IndexController@create')->middleware('contest_account')->name('create');
    Route::get('/{gcode}', 'IndexController@detail')->middleware('auth', 'contest_account')->name('detail');

    Route::get('/{gcode}/analysis', 'IndexController@analysis')->middleware('auth', 'contest_account')->name('analysis');
    Route::get('/{gcode}/analysisDownload', 'IndexController@analysisDownload')->middleware('auth', 'contest_account')->name('analysis.download');
    Route::group(['prefix' => '{gcode}/settings','as' => 'settings.', 'middleware' => ['privileged']], function () {
        Route::get('/', 'AdminController@settings')->middleware('auth', 'contest_account')->name('index');
        Route::get('/general', 'AdminController@settingsGeneral')->middleware('auth', 'contest_account')->name('general');
        Route::get('/return', 'AdminController@settingsReturn')->middleware('auth', 'contest_account')->name('return');
        Route::get('/danger', 'AdminController@settingsDanger')->middleware('auth', 'contest_account')->name('danger');
        Route::get('/member', 'AdminController@settingsMember')->middleware('auth', 'contest_account')->name('member');
        Route::get('/contest', 'AdminController@settingsContest')->middleware('auth', 'contest_account')->name('contest');
        Route::get('/problems', 'AdminController@problems')->middleware('auth', 'contest_account')->name('problems');
    });
});

Route::group([
    'namespace' => 'Contest',
    'prefix' => 'contest',
    'as' => 'contest.',
    'middleware' => [
        'contest_account'
    ]
], function () {
    Route::get('/', 'IndexController@index')->name('index');
    Route::get('/{cid}', 'IndexController@detail')->name('detail');

    Route::get('/{cid}/board', 'BoardController@board')->middleware('auth')->name('board');
    Route::get('/{cid}/board/challenge', 'BoardController@challenge')->middleware('auth')->name('challenge');
    Route::get('/{cid}/board/challenge/{ncode}', 'BoardController@editor')->middleware('auth')->name('editor');
    Route::get('/{cid}/board/rank', 'BoardController@rank')->middleware('auth')->name('rank');
    Route::get('/{cid}/board/status', 'BoardController@status')->middleware('auth')->name('status');
    Route::get('/{cid}/board/clarification', 'BoardController@clarification')->middleware('auth')->name('clarification');
    Route::get('/{cid}/board/print', 'BoardController@print')->middleware('auth')->name('print');
    Route::get('/{cid}/board/analysis', 'BoardController@analysis')->middleware('auth')->name('analysis');

    Route::get('/{cid}/scrollBoard', 'AdminController@scrollBoard')->middleware('auth', 'contest_account', 'privileged')->name('scrollboard');
    Route::get('/{cid}/board/admin', 'AdminController@admin')->middleware('auth', 'privileged')->name('admin');
    Route::get('/{cid}/admin/downloadContestAccountXlsx', 'AdminController@downloadContestAccountXlsx')->middleware('auth')->name('downloadContestAccountXlsx');
    Route::get('/{cid}/admin/refreshContestRank', 'AdminController@refreshContestRank')->middleware('auth')->name('refreshContestRank');
});

Route::group(['prefix' => 'system'], function () {
    Route::redirect('/', '/system/info', 301);
    Route::get('/info', 'SystemController@info')->name('system_info');
});

Route::group(['prefix' => 'rank'], function () {
    Route::get('/', 'RankController@index')->middleware('contest_account')->name('rank_index');
});

Route::group(['namespace' => 'Tool', 'middleware' => ['contest_account']], function () {
    Route::group(['prefix' => 'tool'], function () {
        Route::redirect('/', '/', 301);
        Route::group(['prefix' => 'pastebin'], function () {
            Route::redirect('/', '/tool/pastebin/create', 301);
            Route::get('/create', 'PastebinController@create')->middleware('auth')->name('tool_pastebin_create');
            Route::get('/view/{$code}', 'PastebinController@view')->name('tool_pastebin_view');
        });
        Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax'], function () {
            Route::group(['prefix' => 'pastebin'], function () {
                Route::post('generate', 'PastebinController@generate')->middleware('auth')->name('tool_ajax_pastebin_generate');
            });
        });
    });
    Route::get('/pb/{code}', 'PastebinController@view')->name('tool_pastebin_view_shortlink');
});

Route::group(['prefix' => 'ajax', 'namespace' => 'Ajax'], function () {
    Route::post('submitSolution', 'ProblemController@submitSolution')->middleware('auth', 'throttle:1,0.17');
    Route::post('resubmitSolution', 'ProblemController@resubmitSolution')->middleware('auth', 'throttle:1,0.17');
    Route::post('judgeStatus', 'ProblemController@judgeStatus')->middleware('auth');
    Route::post('manualJudge', 'ProblemController@manualJudge')->middleware('auth');
    Route::post('submitHistory', 'ProblemController@submitHistory')->middleware('auth');
    Route::post('problemExists', 'ProblemController@problemExists')->middleware('auth');
    Route::post('arrangeContest', 'GroupManageController@arrangeContest')->middleware('auth');
    Route::post('joinGroup', 'GroupController@joinGroup')->middleware('auth');
    Route::get('downloadCode', 'ProblemController@downloadCode')->middleware('auth');
    Route::post('submitSolutionDiscussion', 'ProblemController@submitSolutionDiscussion')->middleware('auth');
    Route::post('updateSolutionDiscussion', 'ProblemController@updateSolutionDiscussion')->middleware('auth');
    Route::post('deleteSolutionDiscussion', 'ProblemController@deleteSolutionDiscussion')->middleware('auth');
    Route::post('voteSolutionDiscussion', 'ProblemController@voteSolutionDiscussion')->middleware('auth');
    Route::post('postDiscussion', 'ProblemController@postDiscussion')->middleware('auth');
    Route::post('addComment', 'ProblemController@addComment')->middleware('auth');

    Route::post('search', 'SearchController')->middleware('auth')->name('ajax.search');

    Route::group(['prefix' => 'message'], function () {
        Route::post('unread', 'MessageController@unread')->middleware('auth');
        Route::post('allRead', 'MessageController@allRead')->middleware('auth');
        Route::post('allDelete', 'MessageController@deleteAll')->middleware('auth');
    });

    Route::group(['prefix' => 'group'], function () {
        Route::post('changeNickName', 'GroupController@changeNickName')->middleware('auth');
        Route::post('createGroup', 'GroupController@createGroup')->middleware('auth');
        Route::post('getPracticeStat', 'GroupController@getPracticeStat')->middleware('auth');
        Route::post('eloChangeLog', 'GroupController@eloChangeLog')->middleware('auth');

        Route::post('changeMemberClearance', 'GroupManageController@changeMemberClearance')->middleware('auth');
        Route::post('changeGroupImage', 'GroupManageController@changeGroupImage')->middleware('auth');
        Route::post('changeJoinPolicy', 'GroupManageController@changeJoinPolicy')->middleware('auth');
        Route::post('changeGroupName', 'GroupManageController@changeGroupName')->middleware('auth');
        Route::post('approveMember', 'GroupManageController@approveMember')->middleware('auth');
        Route::post('removeMember', 'GroupManageController@removeMember')->middleware('auth');
        Route::post('inviteMember', 'GroupManageController@inviteMember')->middleware('auth');
        Route::post('createNotice', 'GroupManageController@createNotice')->middleware('auth');
        Route::post('changeSubGroup', 'GroupManageController@changeSubGroup')->middleware('auth');

        Route::post('addProblemTag', 'GroupAdminController@addProblemTag')->middleware('auth');
        Route::post('removeProblemTag', 'GroupAdminController@removeProblemTag')->middleware('auth');
        Route::get('generateContestAccount', 'GroupAdminController@generateContestAccount')->middleware('auth');
        Route::post('refreshElo', 'GroupAdminController@refreshElo')->middleware('auth');
    });

    Route::group(['prefix' => 'contest'], function () {
        Route::get('updateProfessionalRate', 'ContestController@updateProfessionalRate')->middleware('auth');
        Route::post('fetchClarification', 'ContestController@fetchClarification')->middleware('auth');
        Route::post('requestClarification', 'ContestController@requestClarification')->middleware('auth', 'throttle:1,0.34');
        Route::post('registContest', 'ContestController@registContest')->middleware('auth')->name('ajax.contest.registContest');
        Route::post('getAnalysisData', 'ContestController@getAnalysisData')->middleware('auth')->name('ajax.contest.getAnalysisData');

        Route::get('rejudge', 'ContestAdminController@rejudge')->middleware('auth');
        Route::post('details', 'ContestAdminController@details')->middleware('auth');
        Route::post('assignMember', 'ContestAdminController@assignMember')->middleware('auth');
        Route::post('update', 'ContestAdminController@update')->middleware('auth');
        Route::post('issueAnnouncement', 'ContestAdminController@issueAnnouncement')->middleware('auth');
        Route::post('replyClarification', 'ContestAdminController@replyClarification')->middleware('auth');
        Route::post('setClarificationPublic', 'ContestAdminController@setClarificationPublic')->middleware('auth');
        Route::post('generateContestAccount', 'ContestAdminController@generateContestAccount')->middleware('auth');
        Route::post('getScrollBoardData', 'ContestAdminController@getScrollBoardData')->middleware('auth')->name('ajax.contest.getScrollBoardData');
    });

    Route::group(['prefix' => 'submission'], function () {
        Route::post('detail', 'SubmissionController@detail');
        Route::post('share', 'SubmissionController@share');
    });

    Route::group(['prefix' => 'account'], function () {
        Route::post('update_avatar', 'AccountController@updateAvatar')->middleware('auth')->name('account_update_avatar');
        Route::post('change_basic_info', 'AccountController@changeBasicInfo')->middleware('auth')->name('account_change_basic_info');
        Route::post('change_extra_info', 'AccountController@changeExtraInfo')->middleware('auth')->name('account_change_extra_info');
        Route::post('change_password', 'AccountController@changePassword')->middleware('auth')->name('account_change_password');
        Route::post('check_email_cooldown', 'AccountController@checkEmailCooldown')->middleware('auth')->name('account_check_email_cooldown');
        Route::post('save_editor_width', 'AccountController@saveEditorWidth')->middleware('auth')->name('account_save_editor_width');
    });
});

Auth::routes(['verify' => true]);
