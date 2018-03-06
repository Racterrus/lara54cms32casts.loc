<?php

namespace App\Http\Controllers\Backend;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Prediction;
use Auth;
use App\Exceptions\Handler;
use Carbon\Carbon;

class PredictionController extends Controller
{

    protected $limit = 5;
    protected $uploadPath; //для картинок

    public function __construct()
    {
        $this->uploadPath = public_path(config('cms.image.directory'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $onlyTrashed = false;

        if (Auth::user()->can('indexAllPredictions', Prediction::class)) { //проверка права, только администратор может смотреть все посты
            if (($status = $request->get('status')) && $status == 'trash') {
                $predictions = Prediction::onlyTrashed()->with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::onlyTrashed()->count();
                $onlyTrashed = true;
            } elseif ($status == 'published') {
                $predictions = Prediction::published()->with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::published()->count();
            } elseif ($status == 'scheduled') {
                $predictions = Prediction::scheduled()->with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::scheduled()->count();
            } elseif ($status == 'draft') {
                $predictions = Prediction::draft()->with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::draft()->count();
            } else {
                $predictions = Prediction::with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::count();
            }

            $statusList = $this->statusList();

            return view("backend.predictions.index", compact('predictions', 'predictionCount', 'onlyTrashed', 'statusList'));

        } else {//только посты пользователя
            if (($status = $request->get('status')) && $status == 'trash') {
                $predictions = Prediction::onlyTrashed()->with('author')->whereAuthorId(Auth::id())->latest()->paginate($this->limit);
                $predictionCount = Prediction::onlyTrashed()->whereAuthorId(Auth::id())->count();
                $onlyTrashed = true;
            } elseif ($status == 'published') {
                $predictions = Prediction::published()->whereAuthorId(Auth::id())->with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::published()->whereAuthorId(Auth::id())->count();
            } elseif ($status == 'scheduled') {
                $predictions = Prediction::scheduled()->whereAuthorId(Auth::id())->with('author')->latest()->paginate($this->limit);
                $predictionCount = Prediction::scheduled()->whereAuthorId(Auth::id())->count();
            } elseif ($status == 'draft') {
                $predictions = Prediction::draft()->with('author')->whereAuthorId(Auth::id())->latest()->paginate($this->limit);
                $predictionCount = Prediction::draft()->whereAuthorId(Auth::id())->count();
            } else {
                $predictions = Prediction::with('author')->whereAuthorId(Auth::id())->latest()->paginate($this->limit);
                $predictionCount = Prediction::whereAuthorId(Auth::id())->count();
            }

            $statusList = $this->statusList();

            return view("backend.predictions.index", compact('predictions', 'predictionCount', 'onlyTrashed', 'statusList'));
        }
    }

    private function statusList()
    {
        return [
            'all' => Prediction::count(),
            'published' => Prediction::published()->count(),
            'scheduled' => Prediction::scheduled()->count(),
            'draft' => Prediction::draft()->count(),
            'trash' => Prediction::onlyTrashed()->count(),
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Prediction $prediction)
    {
        return view('backend.predictions.create', compact('prediction'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Requests\PredictionRequest $request)
    {

        //сначала - валидация! она спрятана в PostRequest :)

        $data = $this->handleRequest($request); //см. функцию ниже

        if ($request->author_id) {

            Prediction::create($data);
        } else {
            //predictions() - это для отношений, прописано в модели юзера, не требует прописывания author_id в разрешенных полях
            $request->user()->predictions()->create($data);
        }

        //помин про RETURN!!!
        return redirect(route('backend.predictions.index'))->with('message', 'Пост создан успешно!');
    }

    private function handleRequest($request)
    {
        $data = $request->all();
        $data['author_id'] = $request->author_id;
        $data['slug'] = str_slug($request->title);
        //$data['title'] = date("d/m/Y H:i:s");
        //$data['author_id'] = Auth::user()->id;
        //dd($data);

//пока изображения в предсказаниях не используем
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = $image->getClientOriginalName();
            $destination = $this->uploadPath;

            $successUploaded = $image->move($destination, $fileName);

            if ($successUploaded) {
                $width = config('cms.image.thumbnail.width');
                $height = config('cms.image.thumbnail.height');
                $extension = $image->getClientOriginalExtension();
                $thumbnail = str_replace(".{$extension}", "_thumb.{$extension}", $fileName);

                Image::make($destination . '/' . $fileName)
                    ->resize($width, $height)
                    ->save($destination . '/' . $thumbnail);
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $prediction = Prediction::findOrFail($id);

        $this->authorize('editPrediction', $prediction); //проверк права редактировать

        //проверка разрешение по роли и времени с момента СОЗДАНИЯ статьи
        if ((!Auth::user()->can('editAllPrediction', $prediction)) && ($prediction->created_at < Carbon::now()->subMinutes(config('cms.time_for_edit')))) {

            //echo 'Извините, Вы имеете право отредактировать или удалить предсказание только в течении ' . config( 'cms.time_for_edit' ) . ' минут после публикации!';

            return view("backend.predictions.edit_proof", compact('prediction'));
        }

        return view("backend.predictions.edit", compact('prediction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Requests\PredictionRequest $request, $id)
    {

        //Валидация - в реквестах!

        $prediction = Prediction::findOrFail($id);
//		dd(Carbon::now()->addMinutes(3) <= $prediction->published_at );

        //Помни про второй аргумент в can(... !!!
        if (!Auth::user()->can('editAllPrediction', $prediction)) {
            if ($request->published_at !== $prediction->getOriginal('published_at')
                //сюда добавляем проверку строк, которые не имеет права реадактировать предсказатель
                || $request->confirmed !== $prediction->getOriginal('confirmed')
                || $request->author_id !== $prediction->getOriginal('author_id')) {
                abort(403);
            }
        }

        $oldImage = $prediction->image;
        $data = $this->handleRequest($request);
        $prediction->update($data);

        if ($oldImage !== $prediction->image) {
            $this->removeImage($oldImage);
        }

        return redirect(route('backend.predictions.index'))->with('message', 'Your post was updated successfully!');
    }

    //пока картинок у предсказаний нет, метод в общем-то существует только "чтобы был"
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
     * разрешаем только редактировать предсказателю только доказательство и только после того как автор уже не может менять прочие данные
     */
    public function update_proof(Requests\PredictionRequest $request, $id)
    {

        $predictions = Prediction::findOrFail($id);
        //$proof      = Prediction::findOrFail( $id )->get( 'proof' );

        $predictions->proof = $request->proof;
        $predictions->update();

        return redirect(route('backend.predictions.index'))->with('message', 'Доказательство добавлено!');

        //todo 1. блокируем разрешение через 70 минут после указания доказательства
        //todo 2. добавлене нескольких доказательств всеми желающими

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $prediction = Prediction::withTrashed()->findOrFail($id); //только ради проверки

        $this->authorize('destroyPrediction', $prediction); //проверк права на удаление

        Prediction::findOrFail($id)->delete();

        return redirect(route('backend.predictions.index'))->with('trash-message', [
            'Ваше предсказание отправлено в корзину для мусора',
            $id
        ]);
    }

    public function forceDestroy($id)
    {

        $prediction = Prediction::withTrashed()->findOrFail($id);

        $this->authorize('destroyPrediction', $prediction); //проверк права на удаление

        $prediction->forceDelete();

        //пока картинок нету, можно и убрать
        $this->removeImage($prediction->image);

        return redirect('/backend/predictions?status=trash')->with('message', 'Предсказание удалено окончательно!');
    }

    public function restore($id)
    {
        $prediction = Prediction::withTrashed()->findOrFail($id);
        $prediction->restore();

        return redirect()->back()->with('message', 'Предсказание восстановлено из корзины.');
    }
}
