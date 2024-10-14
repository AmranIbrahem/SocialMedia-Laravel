<?php

use App\Http\Controllers\Friends\FriendshipRequestsController;
use App\Http\Controllers\Friends\FriendshipsController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Group\GroupInteractionsCommentController;
use App\Http\Controllers\Group\GroupPostCommentController;
use App\Http\Controllers\Group\GroupPostInteractionsController;
use App\Http\Controllers\Group\GroupRepliesCommentController;
use App\Http\Controllers\Group\PostsGroupController;

use App\Http\Controllers\Page\PageController;
use App\Http\Controllers\Post\CommentsController;
use App\Http\Controllers\Post\InteractionCommentController;
use App\Http\Controllers\Post\InteractionController;
use App\Http\Controllers\Post\PostsController;
use App\Http\Controllers\Post\ReplyCommentController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CoverPhotoController;
use App\Http\Controllers\User\MainPhotoController;
use App\Http\Controllers\User\PrivacyController;
use App\Http\Controllers\User\SavedPostController;
use App\Http\Controllers\User\SearchController;
use App\Http\Controllers\User\StoriesController;
use App\Http\Controllers\User\UserBlockController;
use App\Http\Controllers\User\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*********************** Authentication APIs ***********************/
// Basic Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/EmailVerified/{id}', [AuthController::class, 'EmailVerified']);
Route::post('/forgotPassword', [AuthController::class, 'forgotPassword']);
Route::post('/CheckCodePassword/{idUser}', [AuthController::class, 'CheckCodePassword']);
Route::post('/updatePassword/{idUser}', [AuthController::class, 'updatePassword']);


//////////////////////////////////////////////////////////////////////////////

Route::group(['middleware' => ['jwt.auth','verified']], function () {

    Route::group(['prefix' => 'photos'], function () {
        // To Add Main Image:
        Route::post('/main', [MainPhotoController::class, 'AddMainImage']);

        // To Show All Main Image according to the time of addition from last to first:
        Route::get('/main/{idUser}', [MainPhotoController::class, 'GetMainPhoto']);

        // To Add Cover Image:
        Route::post('/cover', [CoverPhotoController::class, 'AddCoverImage']);

        // To Show All Cover Image according to the time of addition from last to first:
        Route::get('/cover/{idUser}', [CoverPhotoController::class, 'GetCoverPhoto']);
    });

    Route::group(['prefix' => 'friend-requests'], function () {
        // To Send friend requests:
        Route::post('/send/{receiver_user_id}', [FriendshipRequestsController::class, 'sendRequest']);

        // To Show all sent friend requests:
        Route::get('/sent', [FriendshipRequestsController::class, 'GetAllRequestSend']);

        // To Show all received friend requests:
        Route::get('/received', [FriendshipRequestsController::class, 'GetAllRequest']);

        // To Approve friend requests:
        Route::post('/accept/{idUser}', [FriendshipsController::class, 'AcceptRequestsFriend']);
    });

    Route::group(['prefix' => 'friends'], function () {
        // To Show all friends according to the time of addition from first to last:
        Route::get('/time-fl/{idUser}', [FriendshipsController::class, 'ShowFriendTFL']);

        // To Show all friends according to the time of addition from last to first:
        Route::get('/time-lf/{idUser}', [FriendshipsController::class, 'ShowFriendTLF']);

        // To Show all friends in alphabetical order:
        Route::get('/alphabetical/{idUser}', [FriendshipsController::class, 'ShowFriendN']);

        // To Show 6 Friend Random:
        Route::get('/random-six/{idUser}', [FriendshipsController::class, 'ShowFriend6Only']);
    });

    Route::group(['prefix' => 'Search'], function () {
        //To Search By Name:
        Route::get('/SearchByName/{name}', [SearchController::class, 'SearchByName']);

        //To Search By Post:
        Route::get('/SearchByPost/{name}', [SearchController::class, 'searchByPost']);
    });

    Route::group(['prefix' => 'posts'], function () {
        //To create Post:
        Route::post('/createPost', [PostsController::class, 'createPost']);

        //To Get All Post for user:
        Route::get('/GetPosts/{UserId}', [PostsController::class, 'getUserPosts']);

        //Show Post self and get all thing :
        Route::get('/GetPostsSelf/{PostId}', [PostsController::class, 'ShowAllThingAboutPost']);

        //Update Post:
        Route::put('/UpdatePost/{PostId}', [PostsController::class, 'updatePost']);

        //Delete Post:
        Route::delete('/DeletePost/{PostId}', [PostsController::class, 'deletePost']);

        //Get Interaction Post :
        Route::get('/GetInteraction/{PostId}/Post', [PostsController::class, 'GetInteractionPost']);

        //Get Users By Interaction Type for Post :
        Route::get('/getUsersByInteractionTypeForPost/{PostId}/Post/{interactionType}/interaction', [PostsController::class, 'getUsersByInteractionTypeForPost']);

        //Add Post to Save List :
        Route::post('/saved-posts', [SavedPostController::class, 'savePost']);

        //Remove Post to Save List :
        Route::delete('/saved-posts/{post_id}/post', [SavedPostController::class, 'removeSavedPost']);

        // Get All Post from Save List :
        Route::get('/saved-posts', [SavedPostController::class, 'getSavedPosts']);

    });

    Route::group(['prefix' => 'Comments'], function () {
        //Create Comment:
        Route::post('/createComment/{PostId}/Post', [CommentsController::class, 'createComment']);

        //Get All Comments For Post:
        Route::get('/GetComment/{PostId}/Post', [CommentsController::class, 'getPostComments']);

        //Get Count of comment in post :
        Route::get('/GetCountComment/{PostId}/Post', [CommentsController::class, 'countComments']);

        //Delete Comment:
        Route::delete('/DeleteComment/{CommentId}/Comment', [CommentsController::class, 'deleteComment']);

        //Update Comment:
        Route::put('/UpdateComment/{CommentId}/Comment', [CommentsController::class, 'updateComment']);

        //Get All Reply for Comment:
        Route::get('/GetAllReplyForComment/{CommentId}/Comment', [CommentsController::class, 'GetAllReplyForComment']);

        // Get All Interaction for Comment:
        Route::get('/getUsersInteractedOnComment/{CommentId}/comment',[CommentsController::class,'getUsersInteractedOnComment']);

        // Get every thing about comment :
        Route::get('/getEveryThingForComment/{CommentId}/comment',[CommentsController::class,'getEveryThingForComment']);

        // Get users by interaction type :
        Route::get('/{commentId}/interactions/{interactionType}', [CommentsController::class, 'getUsersByInteractionType']);

    });

    Route::group(['prefix' => 'Interaction'], function () {
        //Add Interaction To Post:
        Route::post('/{PostId}/Post',[InteractionController::class,'addInteraction']);

        //Update Interaction To Post:
        Route::put('/UpdateInteraction/{PostId}/Post',[InteractionController::class,'updateInteraction']);

        //Delete Interaction from Post:
        Route::delete('/DeleteInteraction/{PostId}/Post',[InteractionController::class,'deleteInteraction']);

    });

    Route::group(['prefix' => 'replyComment'], function () {
        //Add Reply to Comment:
        Route::post('/{CommentId}/Comment',[ReplyCommentController::class,'addReply']);

        //Update Reply Comment:
        Route::put('/UpdateReply/{ReplyId}/reply',[ReplyCommentController::class,'updateReply']);

        //Delete Reply Comment:
        Route::delete('/DeleteReply/{ReplyId}/reply',[ReplyCommentController::class,'deleteReply']);

    });

    Route::group(['prefix' => 'interactionComment'], function () {
        //Add Interaction Comment :
        Route::post('/{CommentId}/Comment',[InteractionCommentController::class,'AddInteractionComment']);

        //Edit Interaction Comment :
        Route::put('/{CommentId}/Comment',[InteractionCommentController::class,'updateInteractionComment']);

        //Delete Interaction Comment :
        Route::delete('/{interactionId}/interactionComment',[InteractionCommentController::class,'deleteInteractionComment']);

    });

    Route::group(['prefix' => 'stories'], function () {
        //Create Story :
        Route::post('/create', [StoriesController::class, 'createStory']);

        //Get User Story :
        Route::get('/user/{userId}', [StoriesController::class, 'getUserStories']);

        //Delete Story :
        Route::delete('/delete/{storyId}', [StoriesController::class, 'deleteStory']);

    });

    Route::group(['prefix' => 'profile'], function () {
        //Edit Profile:
        Route::put('/editProfile',[UserProfileController::class,'profile']);

        //Delete Main Photo:
        Route::delete('/deleteMainPhoto/{PhotoId}/Photo',[UserProfileController::class,'deleteMainPhoto']);

        //Delete Cover Photo:
        Route::delete('/deleteCoverPhoto/{PhotoId}/Photo',[UserProfileController::class,'deleteCoverPhoto']);
    });

    Route::group(['prefix' => 'privacy'], function () {
        // Get User Post Interactions:
        Route::get('/post-interactions', [PrivacyController::class, 'userPostInteractions']);

        /// Get User Post Comment:
        Route::get('/post-comments', [PrivacyController::class, 'userPostComments']);

        // Get User Comment Interactions :
        Route::get('/comment-interactions', [PrivacyController::class, 'userCommentInteractions']);

        // Get User Comment Replies :
        Route::get('/comment-replies', [PrivacyController::class, 'userCommentReplies']);

        // Get Blocked Users: :
        Route::get('//blocked-user', [PrivacyController::class, 'blockUser']);

    });

    Route::group(['prefix' => 'block'], function () {
        //Add User To Block User :
        Route::post('/block-user', [UserBlockController::class, 'blockUser']);

        //Remove User To Block User :
        Route::delete('/unblock-user/{blocked_id}', [UserBlockController::class, 'unblockUser']);

        //Get Block List :
        Route::get('/blocked-user', [UserBlockController::class, 'getBlockedUsers']);

    });

    Route::group(['prefix' => 'groups'], function () {
        //To create Group :
        Route::post('/create', [GroupController::class, 'createGroup']);

        //To Get Group :
        Route::get('/{groupId}', [GroupController::class, 'getGroup']);

        //Send Join Request To Group :
        Route::post('/join/{groupId}', [GroupController::class, 'sendJoinRequest']);

        //For leave Group :
        Route::post('/leave/{groupId}/group', [GroupController::class, 'leaveGroup']);

        //For Delete Group :
        Route::delete('/delete/{groupId}/group', [GroupController::class, 'deleteGroup']);

        //For Accept Join Request :
        Route::post('/accept-request/{requestId}', [GroupController::class, 'acceptJoinRequest']);

        //For Reject Join Request :
        Route::post('/reject-request/{requestId}', [GroupController::class, 'rejectJoinRequest']);

        //For Add User To admin Group  :
        Route::put('/update-admins/{groupId}/group', [GroupController::class, 'updateAdmins']);

        //For remove Use From admin Group :
        Route::delete('/{groupId}/admins/{adminId}', [GroupController::class, 'removeAdmin']);

        //For Show All admins in group :
        Route::get('/{groupId}/admins', [GroupController::class, 'getAllAdmins']);

        //For Edit Group Details :
        Route::put('/updateGroupDetails/{groupId}/group', [GroupController::class, 'updateGroupDetails']);

        //To Create Post in group :
        Route::post('/post/create/{groupId}/group', [PostsGroupController::class, 'createPostGroup']);

        //For edit post in group :
        Route::put('/post/{postId}', [PostsGroupController::class, 'updatePostGroup']);

        //For delete Post in group :
        Route::delete('/post/{postId}', [PostsGroupController::class, 'deletePostGroup']);

        //For show all posts in group :
        Route::get('/{groupId}/posts', [PostsGroupController::class, 'getGroupPosts']);

        //To show every thing for post in group :
        Route::get('/GetPostsSelf/{PostId}', [PostsGroupController::class, 'ShowAllThingAboutPost']);

        //To Show Interaction for post in group :
        Route::get('/GetInteraction/{PostId}/Post', [PostsGroupController::class, 'GetInteractionPostGroup']);

    });

    Route::group(['prefix' => 'group/comments'], function () {
        //For Add Comment to Post in group :
        Route::post('/create/{postId}/post', [GroupPostCommentController::class, 'createCommentGroup']);

        //For Show Comment in post in group :
        Route::get('/{postId}/post', [GroupPostCommentController::class, 'getPostCommentsGroup']);

        //For Remove Comment in post in group:
        Route::delete('/{commentId}/comment', [GroupPostCommentController::class, 'deleteCommentGroup']);

        //For Edit Comment in post in group:
        Route::put('/{commentId}/comment', [GroupPostCommentController::class, 'updateCommentGroup']);

        //To Show All replies in comment in group :
        Route::get('/GetAllReplyForComment/{CommentId}/Comment', [GroupPostCommentController::class, 'GetAllReplyForCommentGroup']);

        //To show all user interaction in comment in group:
        Route::get('/getUsersInteractedOnComment/{CommentId}/comment',[GroupPostCommentController::class,'getUsersInteractedOnCommentGroup']);

        //For show every thing for comment in group :
        Route::get('/getEveryThingForComment/{CommentId}/comment',[GroupPostCommentController::class,'getEveryThingForCommentGroup']);

        //To Show User By Interaction type for comment in group :
        Route::get('/{commentId}/interactions/{interactionType}', [GroupPostCommentController::class, 'getUsersByInteractionTypeGroup']);

    });

    Route::group(['prefix' => 'group-posts'], function () {
        //To Add Interaction To post in group :
        Route::post('/{postId}/interactions', [GroupPostInteractionsController::class, 'addInteractionGroup']);

        //For edit Interaction To post in group :
        Route::put('/{postId}/interactions', [GroupPostInteractionsController::class, 'updateInteractionGroup']);

        //To Remove  Interaction To post in group :
        Route::delete('/{postId}/interactions', [GroupPostInteractionsController::class, 'deleteInteractionGroup']);

    });

    Route::group(['prefix' => 'group-comments'], function () {
        //For Add Interaction To comment in group:
        Route::post('/comments/{CommentId}/interactions', [GroupInteractionsCommentController::class, 'AddInteractionCommentGroup']);

        //For Edit Interaction To comment in group :
        Route::put('/comments/{CommentId}/interactions', [GroupInteractionsCommentController::class, 'updateInteractionCommentGroup']);

        //To Remove Interaction To comment in group :
        Route::delete('/{interactionId}/interactionComment',[GroupInteractionsCommentController::class,'deleteInteractionCommentGroup']);

    });

    Route::group(['prefix' => 'group-reply-comments'], function () {
        //Add Reply to Comment in group:
        Route::post('/{CommentId}/Comment',[GroupRepliesCommentController::class,'addReplyGroup']);

        //Update Reply Comment in group :
        Route::put('/UpdateReply/{ReplyId}/reply',[GroupRepliesCommentController::class,'updateReplyGroup']);

        //Delete Reply Comment in group :
        Route::delete('/DeleteReply/{ReplyId}/reply',[GroupRepliesCommentController::class,'deleteReplyGroup']);

    });

    //// Group routes for pages
    Route::group(['prefix' => 'pages'], function () {
        //For create page:
        Route::post('/create',[PageController::class,'createPage']);

        //To add admins to page:
        Route::post('/{PageId}/admins/add-admins',[PageController::class,'addAdmin']);

        //To Show all admins in page :
        Route::get('/{PageId}/admins',[PageController::class,'getAdmins']);

        //For remove admin from page:
        Route::delete('/{PageId}/admins/{adminId}/remove-admin',[PageController::class,'removeAdmin']);

        //For edit Main Details :
        Route::put('/{PageId}/edit',[PageController::class,'editPageDetailsMain']);

        //For Delete Page :
        Route::delete('/{PageId}',[PageController::class,'destroyPage']);

    });

});

