<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Post;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth;

class BlogController extends BackendController {
	protected $limit = 5;
	protected $uploadPath;

	public function __construct() {
		parent::__construct();
		$this->uploadPath = public_path( config( 'cms.image.directory' ) );
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index( Request $request ) {
		$onlyTrashed = false;

        if (Auth::user()->can('indexAllPosts', Post::class)) { //проверка права, только администратор может смотреть все посты
            if (($status = $request->get('status')) && $status == 'trash') {
                $posts = Post::onlyTrashed()->with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::onlyTrashed()->count();
                $onlyTrashed = true;
            } elseif ($status == 'published') {
                $posts = Post::published()->with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::published()->count();
            } elseif ($status == 'scheduled') {
                $posts = Post::scheduled()->with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::scheduled()->count();
            } elseif ($status == 'draft') {
                $posts = Post::draft()->with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::draft()->count();
            } else {
                $posts = Post::with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::count();
            }

            $statusList = $this->statusList();

            return view("backend.blog.index", compact('posts', 'postCount', 'onlyTrashed', 'statusList'));

        } else {//только посты пользователя
            if (($status = $request->get('status')) && $status == 'trash') {
                $posts = Post::onlyTrashed()->with('category', 'author')->whereAuthorId(Auth::id())->latest()->paginate($this->limit);
                $postCount = Post::onlyTrashed()->whereAuthorId(Auth::id())->count();
                $onlyTrashed = true;
            } elseif ($status == 'published') {
                $posts = Post::published()->whereAuthorId(Auth::id())->with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::published()->whereAuthorId(Auth::id())->count();
            } elseif ($status == 'scheduled') {
                $posts = Post::scheduled()->whereAuthorId(Auth::id())->with('category', 'author')->latest()->paginate($this->limit);
                $postCount = Post::scheduled()->whereAuthorId(Auth::id())->count();
            } elseif ($status == 'draft') {
                $posts = Post::draft()->with('category', 'author')->whereAuthorId(Auth::id())->latest()->paginate($this->limit);
                $postCount = Post::draft()->whereAuthorId(Auth::id())->count();
            } else {
                $posts = Post::with('category', 'author')->whereAuthorId(Auth::id())->paginate($this->limit);
                $postCount = Post::whereAuthorId(Auth::id())->count();
            }

            $statusList = $this->statusList();

            return view("backend.blog.index", compact('posts', 'postCount', 'onlyTrashed', 'statusList'));
        }

    }

	private function statusList() {
		return [
			'all'       => Post::count(),
			'published' => Post::published()->count(),
			'scheduled' => Post::scheduled()->count(),
			'draft'     => Post::draft()->count(),
			'trash'     => Post::onlyTrashed()->count(),
		];
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create( Post $post ) {
		return view( 'backend.blog.create', compact( 'post' ) );
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store( Requests\PostRequest $request ) {
		//сначала - валидация! она спрятана в PostRequest :)

		$data = $this->handleRequest( $request );
//		$image       = $request->file( 'image' );
//		$fileName    = $image->getClientOriginalName();
//		dd($fileName);

		$request->user()->posts()->create( $data );

//помин про RETURN!!!
		return redirect( 'backend/blog/' )->with( 'success', 'Пост создан успешно!' );
	}

	private function handleRequest( $request ) {
		$data = $request->all();

		if ( $request->hasFile( 'image' ) ) {
			$image       = $request->file( 'image' );
			$fileName    = $image->getClientOriginalName();
			$destination = $this->uploadPath;

			$successUploaded = $image->move( $destination, $fileName );

			if ( $successUploaded ) {
				$width     = config( 'cms.image.thumbnail.width' );
				$height    = config( 'cms.image.thumbnail.height' );
				$extension = $image->getClientOriginalExtension();
				$thumbnail = str_replace( ".{$extension}", "_thumb.{$extension}", $fileName );

				Image::make( $destination . '/' . $fileName )
				     ->resize( $width, $height )
				     ->save( $destination . '/' . $thumbnail );
			}

			$data['image'] = $fileName;
		}

		return $data;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show( $id ) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit( $id ) {

		$post = Post::findOrFail( $id );

        $this->authorize('editPost', $post); //проверк права

		return view( "backend.blog.edit", compact( 'post' ) );
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update( Requests\PostRequest $request, $id ) {

        $post = Post::findOrFail($id);
		$oldImage = $post->image;
		$data     = $this->handleRequest( $request );
		$post->update( $data );

		if ( $oldImage !== $post->image ) {
			$this->removeImage( $oldImage );
		}

		return redirect( '/backend/blog' )->with( 'message', 'Your post was updated successfully!' );
	}

    private function removeImage($image)
    {
        if (!empty($image)) {
            $imagePath = $this->uploadPath . '/' . $image;
            $ext = substr(strrchr($image, '.'), 1);
            $thumbnail = str_replace(".{$ext}", "_thumb.{$ext}", $image);
            $thumbnailPath = $this->uploadPath . '/' . $thumbnail;

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy( $id ) {
		//находим пост по id и удаляем в корзину
		Post::findOrFail( $id )->delete();

		return redirect( '/backend/blog' )->with( 'trash-message', [ 'Your post moved to Trash', $id ] );
	}

	public function forceDestroy( $id ) {
		$post = Post::withTrashed()->findOrFail( $id );
		$post->forceDelete();

		$this->removeImage( $post->image );

		return redirect( '/backend/blog?status=trash' )->with( 'message', 'Your post has been deleted successfully' );
	}

	public function restore( $id ) {
		$post = Post::withTrashed()->findOrFail( $id );
		$post->restore();

		return redirect()->back()->with( 'message', 'You post has been moved from the Trash' );
	}
}
