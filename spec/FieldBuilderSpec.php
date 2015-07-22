<?php

namespace spec\Styde\Html;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Styde\Html\Access\AccessHandler;
use Styde\Html\FormBuilder;
use Styde\Html\Theme;
use Illuminate\Translation\Translator as Lang;

class FieldBuilderSpec extends ObjectBehavior
{
    function let(FormBuilder $form, Theme $theme, Lang $lang)
    {
        $this->beConstructedWith($form, $theme, $lang);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Styde\Html\FieldBuilder');
    }

    function it_generates_a_text_field($form, $theme, $lang)
    {
        // Expect
        $form->text("name", "value", ["class" => "", "id" => "name"])
            ->shouldBeCalled()
            ->willReturn('<input>');

        $lang->get('validation.attributes.name')
            ->shouldBeCalled()
            ->willReturn('validation.attributes.name');

        $theme->render(null, [
            "htmlName" => "name",
            "id" => "name",
            "label" => "Name",
            "input" => "<input>",
            "errors" => [],
            "hasErrors" => false,
            "required" => false
        ], "fields.default")->shouldBeCalled()->willReturn('html');

        // When
        $this->text('name', 'value')->shouldReturn('html');
    }

    function it_checks_for_access(AccessHandler $access)
    {
        $this->setAccessHandler($access);
        $access->check([])->shouldBeCalled()->willReturn(false);
        $this->text('name', 'value')->shouldReturn('');
    }

    function it_generates_a_text_field_with_abbreviated_options($form, $theme, $lang)
    {
        // Having
        $this->setAbbreviations(['ph' => 'placeholder']);
        $placeholder = "Write your name";

        // Expect
        $form->text("name", "value", Argument::withEntry('placeholder', $placeholder))
            ->shouldBeCalled();

        // When
        $this->text('name', 'value', ['ph' => $placeholder]);
    }

    function it_generates_a_text_field_with_a_custom_label($form, $theme, $lang)
    {
        //Having
        $label = "Full name";

        // Expect
        $lang->get('validation.attributes.name')->shouldNotBeCalled();
        $theme->render(null, Argument::withEntry('label', $label), "fields.default")
            ->shouldBeCalled();

        // When
        // Call Field::text with a custom label
        $this->text('name', 'value', ['label' => $label]);
    }

    function it_generates_a_select_field($form, $theme)
    {
        // Having
        $attributes = ['empty' => '', 'label' => 'Gender'];
        $options = ['m' => 'Male', 'f' => 'Female'];
        $result = array_merge(['' => ''], $options);

        // Expectc
        $form->select("gender", $result, null, ["class" => "", "id" => "gender"])->shouldBeCalled();

        // When
        $this->select('gender', $options, null, $attributes);
    }

    function it_adds_an_empty_option_to_select_fields($form, $lang)
    {
        // Having
        $empty = 'Select option';
        $options = ['m' => 'Male', 'f' => 'Female'];
        $result = array_merge(['' => $empty], $options);

        // Expec
        $lang->get("validation.empty_option.gender")
            ->shouldBeCalled()
            ->willReturn("validation.empty_option.gender");

        $lang->get("validation.empty_option.default")
            ->shouldBeCalled()
            ->willReturn($empty);

        $form->select("gender", $result, "m", ["class" => "", "id" => "gender"])
            ->shouldBeCalled()
            ->willReturn('<select>');

        // When
        $this->select('gender', $options, 'm', ['label' => 'Gender']);
    }

    function it_generates_a_text_field_with_errors($form, $theme, $lang)
    {
        // Having
        $errors = ['This is really wrong'];
        $this->setErrors([
            'name' => $errors
        ]);

        // Expect
        $form->text("name", "value", ["class" => "error", "id" => "name"])->shouldBeCalled();
        $theme->render(
            null,
            Argument::withEntry('errors', $errors),
            "fields.default"
        )->shouldBeCalled();

        // When
        $this->text('name', 'value');
    }

    function it_takes_select_options_from_the_model($form, User $user)
    {
        // Having
        $attributes = ['empty' => '', 'label' => 'Gender'];
        $options = ['m' => 'Male', 'f' => 'Female'];
        $result = array_merge(['' => ''], $options);

        // Expect
        $form->getModel()->shouldBeCalled()->willReturn($user);
        $user->getGenderOptions()->shouldBeCalled()->willReturn($options);
        $form->select("gender", $result, "m", ["class" => "", "id" => "gender"])->shouldBeCalled();

        // When
        $this->select('gender', null, 'm', $attributes);
    }

}

interface User {

    public function getGenderOptions();

}